<?php

declare(strict_types=1);

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc;

use Pandoc\Exception\ConversionFailedException;
use Pandoc\Exception\PandocException;
use Pandoc\Exception\PandocNotInstalledException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

final class PandocBinary implements BinaryInterface
{
    private static ?self $defaultInstance = null;
    /**
     * @var (
     *   array{
     *     version: string|null,
     *     binary_path: string,
     *     input_formats: array<string>,
     *     output_formats: array<string>,
     *     highlight_languages: array<string>,
     *     highlight_styles: array<string>,
     *     error?: string,
     *     loaded_at: int,
     *   }|array{}
     * )
     */
    private array $capabilities = [];

    /**
     * @param array{
     *     version: string|null,
     *     binary_path: string,
     *     input_formats: array<string>,
     *     output_formats: array<string>,
     *     highlight_languages: array<string>,
     *     highlight_styles: array<string>,
     *     error?: string,
     *     loaded_at: int,
     * }|array{}|null $capabilities
     */
    public function __construct(
        private readonly string $binaryPath,
        ?array $capabilities = null,
    ) {
        if (null !== $capabilities) {
            $this->capabilities = $capabilities;
        } else {
            $this->capabilities = [];
        }
    }

    public static function create(): self
    {
        return self::$defaultInstance ??= new self(self::detectBinary());
    }

    public static function fromPath(string $binaryPath): self
    {
        if (!is_executable($binaryPath)) {
            throw new PandocNotInstalledException("Pandoc binary not executable: {$binaryPath}");
        }

        return new self($binaryPath);
    }

    public function convert(Conversion $conversion): Conversion
    {
        $startTime = microtime(true);

        try {
            $content = $this->execute($conversion);
            $duration = microtime(true) - $startTime;

            return $conversion->markExecuted(
                success: true,
                duration: $duration,
                outputPath: $conversion->getPath(),
                outputContent: $content
            );
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;

            return $conversion->markExecuted(
                success: false,
                duration: $duration,
                error: $e->getMessage()
            );
        }
    }

    public function execute(Conversion $conversion): string
    {
        $args = $conversion->toCommandArgs();
        $process = new Process([$this->binaryPath, ...$args]);

        if ($conversion->isStringInput()) {
            $process->setInput($conversion->getInputContent());
        }

        $process->run();

        if (!$process->isSuccessful()) {
            $error = $process->getErrorOutput() ?: $process->getOutput();
            throw new ConversionFailedException(message: 'Pandoc conversion failed', pandocError: $error, inputFile: $conversion->inputPath, outputFile: $conversion->outputPath);
        }

        return $process->getOutput();
    }

    public function supports(string $from, string $to): bool
    {
        $capabilities = $this->getCapabilities();

        return \in_array($from, $capabilities['input_formats'], true)
            && \in_array($to, $capabilities['output_formats'], true);
    }

    public function getVersion(): string
    {
        $process = new Process([$this->binaryPath, '--version']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new PandocException('Failed to get pandoc version');
        }

        if (preg_match('/pandoc\s+([\d.]+)/', $process->getOutput(), $matches)) {
            return $matches[1];
        }

        throw new PandocException('Could not parse pandoc version');
    }

    /**
     * @return list<string>
     */
    public function getInputFormats(): array
    {
        return $this->runListCommand('--list-input-formats', 'Failed to get input formats');
    }

    /**
     * @return list<string>
     */
    public function getOutputFormats(): array
    {
        return $this->runListCommand('--list-output-formats', 'Failed to get output formats');
    }

    /**
     * @return list<string>
     */
    public function getHighlightLanguages(): array
    {
        return $this->runListCommand('--list-highlight-languages', graceful: true);
    }

    /**
     * @return list<string>
     */
    public function getHighlightStyles(): array
    {
        return $this->runListCommand('--list-highlight-styles', graceful: true);
    }

    /**
     * Returns the capabilities of the Pandoc binary.
     *
     * @return array{
     *     version: string|null,
     *     binary_path: string,
     *     input_formats: array<string>,
     *     output_formats: array<string>,
     *     highlight_languages: array<string>,
     *     highlight_styles: array<string>,
     *     error?: string,
     *     loaded_at: int,
     * }
     */
    public function getCapabilities(): array
    {
        return $this->capabilities ?: ($this->capabilities = $this->loadCapabilities());
    }

    public function getBinaryPath(): string
    {
        return $this->binaryPath;
    }

    public static function isInstalled(): bool
    {
        try {
            self::detectBinary();

            return true;
        } catch (PandocNotInstalledException) {
            return false;
        }
    }

    private static function detectBinary(): string
    {
        $finder = new ExecutableFinder();

        if ($path = $finder->find('pandoc', null, self::getAdditionalPaths())) {
            if (self::isValidPandocBinary($path)) {
                return $path;
            }
        }

        throw new PandocNotInstalledException('Pandoc not found. Please install from https://pandoc.org/installing.html');
    }

    /**
     * @return list<string>
     */
    private static function getAdditionalPaths(): array
    {
        $paths = [
            '/usr/local/bin/pandoc',
            '/usr/bin/pandoc',
            '/opt/homebrew/bin/pandoc',
            '/home/linuxbrew/.linuxbrew/bin/pandoc',
        ];

        if ($home = getenv('HOME')) {
            $paths[] = $home.'/.local/bin/pandoc';
            $paths[] = $home.'/.cabal/bin/pandoc';
        }

        return $paths;
    }

    private static function isValidPandocBinary(string $path): bool
    {
        if (!is_executable($path)) {
            return false;
        }

        try {
            $process = new Process([$path, '--version']);
            $process->run();

            return $process->isSuccessful() && str_contains($process->getOutput(), 'pandoc');
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * @return list<string>
     */
    private function runListCommand(string $command, string $errorMessage = '', bool $graceful = false): array
    {
        $process = new Process([$this->binaryPath, $command]);
        $process->run();

        if (!$process->isSuccessful()) {
            if ($graceful) {
                return [];
            }
            throw new PandocException($errorMessage);
        }

        return array_values(array_filter(array_map('trim', explode("\n", $process->getOutput()))));
    }

    /**
     * @return array{
     *     version: string|null,
     *     binary_path: string,
     *     input_formats: array<string>,
     *     output_formats: array<string>,
     *     highlight_languages: array<string>,
     *     highlight_styles: array<string>,
     *     error?: string,
     *     loaded_at: int,
     * }
     */
    private function loadCapabilities(): array
    {
        try {
            return [
                'version' => $this->getVersion(),
                'binary_path' => $this->binaryPath,
                'input_formats' => $this->getInputFormats(),
                'output_formats' => $this->getOutputFormats(),
                'highlight_languages' => $this->getHighlightLanguages(),
                'highlight_styles' => $this->getHighlightStyles(),
                'loaded_at' => time(),
            ];
        } catch (\Exception $e) {
            return [
                'version' => null,
                'binary_path' => $this->binaryPath,
                'input_formats' => [],
                'output_formats' => [],
                'highlight_languages' => [],
                'highlight_styles' => [],
                'error' => $e->getMessage(),
                'loaded_at' => time(),
            ];
        }
    }
}
