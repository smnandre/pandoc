<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Convertor;

use Pandoc\Options;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

abstract class ProcessConvertor implements ConvertorInterface
{
    private string $executable;

    private Options $options;

    private LoggerInterface $logger;

    public function __construct(
        ?string $executable = null,
        ?Options $options = null,
        ?LoggerInterface $logger = null,
    ) {
        if (null !== $executable) {
            $this->executable = $executable;
        }
        $this->options = $options ?? Options::create();
        $this->logger = $logger ?? new NullLogger();
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
    private function list(string $string): array
    {
        $lines = explode("\n", $string);

        return array_values(array_filter($lines));
    }

    /**
     * @return list<string>
     */
    public function listHighlightStyles(): array
    {
        $output = $this->run('--list-highlight-styles');

        return $this->list($output);
    }

    /**
     * @return list<string>
     */
    public function listExtensions(?string $format = null): array
    {
        $format = $format ? [$format] : [];
        $output = $this->run('--list-extensions', ...$format);

        return $this->list($output);
    }

    private function run(string... $args): string
    {
        $pandoc = $this->getExecutable();
        $process = new Process([$pandoc, ...$args]);

        $this->logger->debug(sprintf('Running Pandoc command: %s', $process->getCommandLine()));

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            $this->logger->error(sprintf('Failed to run Pandoc command: %s', $e->getMessage()));
        }

        if (!$process->isSuccessful()) {
            // TODO use ExitCode
            throw new \RuntimeException('Pandoc error: ' . $process->getErrorOutput());
        }

        return $process->getOutput();
    }

    private function getExecutable(): string
    {
        if (isset($this->executable)) {
            return $this->executable;
        }

        $pandoc = (new ExecutableFinder())->find('pandoc');
        if (null === $pandoc) {
            throw new \RuntimeException('Pandoc executable not found');
        }

        if (!is_executable($pandoc)) {
            throw new \RuntimeException('Pandoc executable is not executable');
        }

        return $this->executable = $pandoc;
    }

    public function getHelp(): string
    {
        return $this->run('--help');
    }

    public function getVersion(): string
    {
        return $this->run('--version');
    }

    public function convert(string $input, string $output, ?Options $options = null): string
    {
        $options = [
            ...$this->options->toArray(),
            ...($options ? $options->toArray() : []),
        ];
        return $this->run('-f', $input, '-t', $output, ...$options);
    }
}
