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
            if ($outputDir === null && count($input) > 1) {
                throw new ConversionException('Output directory must be specified when converting multiple files.');
            }

            foreach ($input as $file) {
                $this->runPandoc($options, $file);
            }
        } else {
            if (!is_iterable($input)) {
                $input = [$input];
            }

            foreach ($input as $inputFile) {
                if ($output !== null && !is_dir(dirname($output))) {
                    mkdir(dirname($output), 0777, true);
                }
                $this->runPandoc($options, new \SplFileInfo($inputFile));
            }
        }
    }

    private function runPandoc(Options $options, \SplFileInfo $inputFile): void
    {
        $output = $options->getOutput();
        $outputDir = $options->getOutputDir();

        if ($outputDir !== null) {
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            $output = rtrim($outputDir, '/') . '/' . $inputFile->getBasename('.' . $inputFile->getExtension()) . '.' . ($options->getFormat() ?? 'html');
        }

        $process = $this->createProcess($options, $inputFile->getRealPath(), $output);


        try {
            $process->mustRun();

            // Check for error output even if mustRun() succeeded
            if (!empty($process->getErrorOutput())) {
                $exitCode = ExitCode::tryFrom($process->getExitCode());

                $this->logger->error('Pandoc conversion failed (no exception thrown): {message}', [
                    'message' => $process->getErrorOutput(),
                    'command' => $process->getCommandLine(),
                    'exitCode' => $exitCode ? $exitCode->value : null,
                    'exitCodeName' => $exitCode ? $exitCode->name : null,
                    'errorOutput' => $process->getErrorOutput(),
                ]);

                throw new ConversionException(
                    message: 'Pandoc conversion failed. Exit Code: ' . ($exitCode ? $exitCode->value : 'Unknown') . '. Error Output: ' . $process->getErrorOutput(),
                );
            }
        } catch (ProcessFailedException $e) {
            $exitCode = ExitCode::tryFrom($process->getExitCode());

            $this->logger->error('Pandoc conversion failed: {message}', [
                'message' => $e->getMessage(),
                'command' => $process->getCommandLine(),
                'exitCode' => $exitCode ? $exitCode->value : null,
                'exitCodeName' => $exitCode ? $exitCode->name : null,
                'errorOutput' => $process->getErrorOutput(),
            ]);

            throw new ConversionException(
                message: 'Pandoc conversion failed. Exit Code: ' . ($exitCode ? $exitCode->value : 'Unknown'),
                previous: $e,
            );
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

    private function buildCommand(Options $options, string $input, ?string $output): array
    {
        $command = [$this->executable];

        $command[] = (string) $options;

        $command[] = $input;

        if ($output !== null) {
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
