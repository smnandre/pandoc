<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc;

use Pandoc\Configuration\ConversionOptions;
use Pandoc\Converter\ConverterInterface;
use Pandoc\Format\OutputFormat;
use Pandoc\IO\InputSource;
use Pandoc\IO\OutputTarget;
use Pandoc\Result\BatchResult;
use Pandoc\Result\ConversionResult;

/**
 * Batch converter for processing multiple documents efficiently.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class BatchConverter
{
    /**
     * @var array<array{input: InputSource, output: OutputTarget, format: OutputFormat, options: ?ConversionOptions}>
     */
    private array $jobs = [];

    private ?ConversionOptions $batchOptions = null;

    public function __construct(
        private readonly ConverterInterface $converter,
        private readonly ?ConversionOptions $defaultOptions = null,
    ) {}

    /**
     * Add a conversion job to the batch.
     */
    public function add(
        InputSource $input,
        OutputTarget $output,
        OutputFormat $format,
        ?ConversionOptions $options = null
    ): self {
        $this->jobs[] = [
            'input' => $input,
            'output' => $output,
            'format' => $format,
            'options' => $options,
        ];

        return $this;
    }

    /**
     * Set options to apply to all jobs in the batch.
     */
    public function withOptions(ConversionOptions $options): self
    {
        $this->batchOptions = $options;
        return $this;
    }

    /**
     * Execute all batch jobs.
     */
    public function execute(): BatchResult
    {
        if (empty($this->jobs)) {
            return BatchResult::success([]);
        }

        $startTime = microtime(true);
        $results = [];
        $errors = [];

        foreach ($this->jobs as $index => $job) {
            try {
                $converter = new DocumentConverter($this->converter, $this->defaultOptions);
                $finalOptions = $this->mergeBatchOptions($job['options']);
                
                $result = $converter->convert(
                    $job['input'],
                    $job['output'],
                    $job['format'],
                    $finalOptions
                );

                $results[] = $result;
            } catch (\Exception $e) {
                $errors[] = "Job {$index}: " . $e->getMessage();
            }
        }

        $totalDuration = microtime(true) - $startTime;

        if (empty($errors)) {
            return BatchResult::success($results, $totalDuration);
        } else {
            return BatchResult::withFailures($results, $errors, $totalDuration);
        }
    }

    /**
     * Execute jobs with progress callback.
     *
     * @param callable(int, int, ?ConversionResult, ?string): void $progressCallback
     */
    public function executeWithProgress(callable $progressCallback): BatchResult
    {
        if (empty($this->jobs)) {
            return BatchResult::success([]);
        }

        $startTime = microtime(true);
        $results = [];
        $errors = [];
        $total = count($this->jobs);

        foreach ($this->jobs as $index => $job) {
            try {
                $converter = new DocumentConverter($this->converter, $this->defaultOptions);
                $finalOptions = $this->mergeBatchOptions($job['options']);
                
                $result = $converter->convert(
                    $job['input'],
                    $job['output'],
                    $job['format'],
                    $finalOptions
                );

                $results[] = $result;
                $progressCallback($index + 1, $total, $result, null);
            } catch (\Exception $e) {
                $error = "Job {$index}: " . $e->getMessage();
                $errors[] = $error;
                $progressCallback($index + 1, $total, null, $error);
            }
        }

        $totalDuration = microtime(true) - $startTime;

        if (empty($errors)) {
            return BatchResult::success($results, $totalDuration);
        } else {
            return BatchResult::withFailures($results, $errors, $totalDuration);
        }
    }

    /**
     * Get number of jobs in the batch.
     */
    public function getJobCount(): int
    {
        return count($this->jobs);
    }

    /**
     * Clear all jobs from the batch.
     */
    public function clear(): self
    {
        $this->jobs = [];
        return $this;
    }

    /**
     * Create a batch with common input directory and output directory.
     */
    public static function fromDirectories(
        string $inputDir,
        string $outputDir,
        OutputFormat $format,
        string $pattern = '*.md',
        ?ConversionOptions $options = null
    ): self {
        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files()->in($inputDir)->name($pattern);

        $batch = new self(new \Pandoc\Converter\Process\ProcessConverter());
        
        foreach ($finder as $file) {
            $input = InputSource::file($file->getRealPath());
            $outputPath = $outputDir . DIRECTORY_SEPARATOR . 
                         $file->getBasename('.' . $file->getExtension()) . '.' . $format->getExtension();
            $output = OutputTarget::file($outputPath);
            
            $batch->add($input, $output, $format, $options);
        }

        return $batch;
    }

    /**
     * Create a batch from a list of input files.
     *
     * @param array<string> $inputFiles
     */
    public static function fromFiles(
        array $inputFiles,
        string $outputDir,
        OutputFormat $format,
        ?ConversionOptions $options = null
    ): self {
        $batch = new self(new \Pandoc\Converter\Process\ProcessConverter());

        foreach ($inputFiles as $inputFile) {
            $input = InputSource::file($inputFile);
            $basename = pathinfo($inputFile, PATHINFO_FILENAME);
            $outputPath = $outputDir . DIRECTORY_SEPARATOR . $basename . '.' . $format->getExtension();
            $output = OutputTarget::file($outputPath);
            
            $batch->add($input, $output, $format, $options);
        }

        return $batch;
    }

    private function mergeBatchOptions(?ConversionOptions $jobOptions): ?ConversionOptions
    {
        if ($this->batchOptions === null) {
            return $jobOptions;
        }

        if ($jobOptions === null) {
            return $this->batchOptions;
        }

        return $this->batchOptions->merge($jobOptions);
    }
}