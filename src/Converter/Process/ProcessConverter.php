<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Converter\Process;

use Pandoc\Converter\ConverterInterface;
use Pandoc\Exception\ConversionException;
use Pandoc\Options;
use Pandoc\PandocInfo;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * @author Simon André <smn.andre@gmail.com>
 */
final class ProcessConverter implements ConverterInterface
{
    private readonly string $executable;
    private readonly LoggerInterface $logger;

    public function __construct(?string $executable = null, ?LoggerInterface $logger = null)
    {
        if (null === $executable ??= (new PandocExecutableFinder())->find()) {
            throw new ConversionException('Pandoc executable not found.');
        }
        $this->executable = $executable;

        $this->logger = $logger ?? new NullLogger();
    }

    public function convert(Options $options): void
    {
        $input = $options->getInput();
        $output = $options->getOutput();
        $outputDir = $options->getOutputDir();

        if ($input instanceof Finder) {
            if (null === $outputDir && \count($input) > 1) {
                throw new ConversionException('Output directory must be specified when converting multiple files.');
            }

            foreach ($input as $file) {
                $this->runPandoc($options, $file);
            }
        } else {
            foreach ($input as $inputFile) {
                if (null !== $output && !is_dir(\dirname($output))) {
                    mkdir(\dirname($output), 0777, true);
                }
                if (!file_exists($inputFile)) {
                    throw new ConversionException('Input file not found: '.$inputFile);
                }

                $this->runPandoc($options, new \SplFileInfo($inputFile));
            }
        }
    }

    /**
     * @throws ConversionException
     */
    public function getPandocInfo(): PandocInfo
    {
        $process = new Process([$this->executable, '--version']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ConversionException('Could not retrieve Pandoc information. Make sure Pandoc is installed and accessible.');
        }

        $output = $process->getOutput();

        preg_match('/pandoc ([\d.]+)/', $output, $matches);
        $version = $matches[1] ?? '';

        preg_match('/Scripting engine: Lua ([\d.]+)/', $output, $matches);
        $luaVersion = $matches[1] ?? '';

        return new PandocInfo(
            $this->executable,
            $version,
            $luaVersion,
        );
    }

    /**
     * @return list<string>
     */
    public function listInputFormats(): array
    {
        $output = $this->run('--list-input-formats');

        return $this->list($output);
    }

    /**
     * @return list<string>
     */
    public function listOutputFormats(): array
    {
        $output = $this->run('--list-output-formats');

        return $this->list($output);
    }

    /**
     * @return list<string>
     */
    public function listHighlightLanguages(): array
    {
        $output = $this->run('--list-highlight-languages');

        return $this->list($output);
    }

    /**
     * @return list<string>
     */
    public function listHighlightStyles(): array
    {
        $output = $this->run('--list-highlight-styles');

        return $this->list($output);
    }

    private function run(string ...$args): string
    {
        $pandoc = $this->executable;

        $process = new Process([$pandoc, ...$args]);

        $process->run();

        if (!$process->isSuccessful()) {
            $exitCode = ExitCode::tryFrom($process->getExitCode() ?? -1);
            throw new ConversionException(trim($exitCode?->name.' '.$process->getErrorOutput()), $exitCode->value ?? -1);
        }

        return $process->getOutput();
    }

    /**
     * @return list<string>
     */
    private function list(string $string): array
    {
        $lines = explode("\n", $string);

        return array_values(array_filter($lines));
    }

    private function runPandoc(Options $options, \SplFileInfo $inputFile): void
    {
        $output = $options->getOutput();
        $outputDir = $options->getOutputDir();

        if (null !== $outputDir) {
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            $output = rtrim($outputDir, '/').'/'.$inputFile->getBasename('.'.$inputFile->getExtension()).'.'.($options->getFormat() ?? 'html');
        }

        $process = $this->createProcess($options, $inputFile->getRealPath(), $output);

        try {
            $process->mustRun();

            if ($process->getErrorOutput() && \is_int($process->getExitCode())) {
                $exitCode = ExitCode::tryFrom($process->getExitCode());

                $this->logger->error('Pandoc conversion failed (no exception thrown): {message}', [
                    'message' => $process->getErrorOutput(),
                    'command' => $process->getCommandLine(),
                    'exitCode' => $exitCode ? $exitCode->value : null,
                    'exitCodeName' => $exitCode ? $exitCode->name : null,
                    'errorOutput' => $process->getErrorOutput(),
                ]);

                throw new ConversionException(message: 'Pandoc conversion failed. Exit Code: '.($exitCode ? $exitCode->value : 'Unknown').'. Error Output: '.$process->getErrorOutput());
            }
        } catch (ProcessFailedException $e) {
            $exitCode = ExitCode::tryFrom($process->getExitCode() ?? -1);

            $this->logger->error('Pandoc conversion failed: {message}', [
                'message' => $e->getMessage(),
                'command' => $process->getCommandLine(),
                'exitCode' => $exitCode ? $exitCode->value : null,
                'exitCodeName' => $exitCode ? $exitCode->name : null,
                'errorOutput' => $process->getErrorOutput(),
            ]);

            throw new ConversionException(message: 'Pandoc conversion failed. Exit Code: '.($exitCode ? $exitCode->value : 'Unknown'), previous: $e);
        }
    }

    private function createProcess(Options $options, string $input, ?string $output): Process
    {
        $command = $this->buildCommand($options, $input, $output);
        $process = new Process($command);
        $process->setTimeout(null); // No timeout

        $this->logger->debug('Executing Pandoc command: {command}', ['command' => $process->getCommandLine()]);

        return $process;
    }

    /**
     * @return array<string>
     */
    private function buildCommand(Options $options, string $input, ?string $output): array
    {
        $command = [$this->executable];

        $command[] = (string) $options;

        $command[] = $input;

        if (null !== $output) {
            $command[] = '-o';
            $command[] = $output;
        }

        if ($format = $options->getFormat()) {
            $command[] = '-t';
            $command[] = $format;
        }

        return array_filter($command);
    }
}
