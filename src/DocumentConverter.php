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
use Pandoc\Converter\Process\ProcessConverter;
use Pandoc\Format\OutputFormat;
use Pandoc\IO\InputSource;
use Pandoc\IO\InputSourceType;
use Pandoc\IO\OutputTarget;
use Pandoc\IO\OutputTargetType;
use Pandoc\Result\ConversionResult;
use Pandoc\Result\DocumentMetadata;

/**
 * Modern document converter with improved API design.
 *
 * This is the new main entry point for document conversions, providing
 * better separation of concerns and type safety compared to the legacy API.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class DocumentConverter
{
    private ConverterInterface $converter;
    private ?ConversionOptions $defaultOptions;

    public function __construct(?ConverterInterface $converter = null, ?ConversionOptions $defaultOptions = null)
    {
        $this->converter = $converter ?? new ProcessConverter();
        $this->defaultOptions = $defaultOptions;
    }

    /**
     * Create a new document converter instance.
     */
    public static function create(?ConverterInterface $converter = null, ?ConversionOptions $defaultOptions = null): self
    {
        return new self($converter, $defaultOptions);
    }

    /**
     * Convert documents with the new API.
     */
    public function convert(
        InputSource $input,
        OutputTarget $output,
        OutputFormat $format,
        ?ConversionOptions $options = null,
    ): ConversionResult {
        $startTime = microtime(true);

        // Merge options
        $finalOptions = $this->mergeOptions($options);

        // Validate input/output compatibility
        $this->validateConversion($input, $output);

        try {
            if ($input->isMultiple()) {
                return $this->convertMultiple($input, $output, $format, $finalOptions, $startTime);
            } else {
                return $this->convertSingle($input, $output, $format, $finalOptions, $startTime);
            }
        } catch (\Exception $e) {
            throw new Exception\ConversionException(
                'Document conversion failed: ' . $e->getMessage(),
                0,
                $e,
            );
        }
    }

    /**
     * Convert documents with metadata.
     */
    public function convertWithMetadata(
        InputSource $input,
        OutputTarget $output,
        OutputFormat $format,
        DocumentMetadata $metadata,
        ?ConversionOptions $options = null,
    ): ConversionResult {
        // For now, we'll add metadata as variables to the options
        // In a real implementation, this could write a temporary metadata file
        $enhancedOptions = $options ?? ConversionOptions::create();

        // Add metadata as variables
        if ($metadata->getTitle()) {
            $enhancedOptions = $enhancedOptions->variable('title', $metadata->getTitle());
        }
        if ($metadata->getAuthor()) {
            $enhancedOptions = $enhancedOptions->variable('author', $metadata->getAuthor());
        }
        if ($metadata->getDate()) {
            $enhancedOptions = $enhancedOptions->variable('date', $metadata->getDate()->format('Y-m-d'));
        }

        return $this->convert($input, $output, $format, $enhancedOptions);
    }

    /**
     * Get a batch converter for multiple operations.
     */
    public function batch(): BatchConverter
    {
        return new BatchConverter($this->converter, $this->defaultOptions);
    }

    /**
     * Get converter capabilities.
     */
    public function getCapabilities(): ConverterCapabilities
    {
        return new ConverterCapabilities($this->converter);
    }

    private function convertSingle(
        InputSource $input,
        OutputTarget $output,
        OutputFormat $format,
        ConversionOptions $options,
        float $startTime,
    ): ConversionResult {
        if ($output->returnsString()) {
            return $this->convertToString($input, $format, $options, $startTime);
        }

        // Convert using legacy Options for compatibility
        $legacyOptions = $this->createLegacyOptions($input, $output, $format, $options);

        $this->converter->convert($legacyOptions);

        $duration = microtime(true) - $startTime;
        $outputPaths = $output->getType() === \Pandoc\IO\OutputTargetType::FILE ? [$output->getTarget()] : [];

        return ConversionResult::fileResult($outputPaths, null, $duration);
    }

    private function convertMultiple(
        InputSource $input,
        OutputTarget $output,
        OutputFormat $format,
        ConversionOptions $options,
        float $startTime,
    ): ConversionResult {
        if (!$output->supportsMultipleFiles()) {
            throw new Exception\ConversionException('Output target does not support multiple files');
        }

        $outputPaths = [];
        $filePaths = $input->getFilePaths();

        foreach ($filePaths as $inputPath) {
            $outputPath = $output->generateOutputPath($inputPath, $format);

            $singleInput = InputSource::file($inputPath, $input->getFormat());
            $singleOutput = OutputTarget::file($outputPath);

            $legacyOptions = $this->createLegacyOptions($singleInput, $singleOutput, $format, $options);
            $this->converter->convert($legacyOptions);

            $outputPaths[] = $outputPath;
        }

        $duration = microtime(true) - $startTime;

        return ConversionResult::fileResult($outputPaths, null, $duration);
    }

    private function convertToString(
        InputSource $input,
        OutputFormat $format,
        ConversionOptions $options,
        float $startTime,
    ): ConversionResult {
        // For string output, we'll use a temporary file and read it back
        $tempOutput = OutputTarget::temporary('.' . $format->getExtension());

        try {
            $legacyOptions = $this->createLegacyOptions($input, $tempOutput, $format, $options);
            $this->converter->convert($legacyOptions);

            $outputPath = $tempOutput->getTarget();
            if (!file_exists($outputPath)) {
                throw new Exception\ConversionException(
                    'Expected output file was not created: ' . $outputPath,
                );
            }
            $content = file_get_contents($outputPath);
            if ($content === false) {
                throw new Exception\ConversionException('Failed to read converted content from: ' . $outputPath);
            }

            $duration = microtime(true) - $startTime;

            return ConversionResult::stringResult($content, null, $duration);
        } finally {
            $tempOutput->cleanup();
        }
    }

    private function createLegacyOptions(
        InputSource $input,
        OutputTarget $output,
        OutputFormat $format,
        ConversionOptions $options,
    ): Options {
        $legacyOptions = Options::create();

        // Set input
        if ($input->getType() === InputSourceType::FILE) {
            $source = $input->getSource();
            if (is_string($source) || $source instanceof \SplFileInfo) {
                $legacyOptions = $legacyOptions->setInput([$source]);
            }
        } elseif ($input->getType() === InputSourceType::FILES) {
            $inputSource = $input->getSource();
            if (is_array($inputSource)) {
                $filtered = array_filter($inputSource, function ($item) {
                    return is_string($item) || $item instanceof \SplFileInfo;
                });
                /** @var array<int, string|\SplFileInfo> $filtered */
                $legacyOptions = $legacyOptions->setInput($filtered);
            } elseif (is_iterable($inputSource)) {
                $iterableFiltered = [];
                foreach ($inputSource as $item) {
                    if (is_string($item) || $item instanceof \SplFileInfo) {
                        $iterableFiltered[] = $item;
                    }
                }
                $legacyOptions = $legacyOptions->setInput($iterableFiltered);
            }
        } elseif ($input->getType() === InputSourceType::FINDER) {
            $inputSource = $input->getSource();
            if (is_iterable($inputSource)) {
                $iterableFiltered = [];
                foreach ($inputSource as $item) {
                    if (is_string($item) || $item instanceof \SplFileInfo) {
                        $iterableFiltered[] = $item;
                    }
                }
                $legacyOptions = $legacyOptions->setInput($iterableFiltered);
            } elseif (is_string($inputSource) || $inputSource instanceof \SplFileInfo) {
                $legacyOptions = $legacyOptions->setInput([$inputSource]);
            }
        }

        // Set output
        if ($output->getType() === OutputTargetType::FILE) {
            $legacyOptions = $legacyOptions->setOutput($output->getTarget());
        } elseif ($output->getType() === OutputTargetType::DIRECTORY) {
            $legacyOptions = $legacyOptions->setOutputDir($output->getTarget());
        }

        // Set format
        $legacyOptions = $legacyOptions->setFormat($format->value);

        // Set input format if specified
        if ($input->getFormat()) {
            $legacyOptions = $legacyOptions->from($input->getFormat()->value);
        }

        // Convert new options to legacy options
        foreach ($options->toArray() as $key => $value) {
            $legacyOptions = $legacyOptions->setOption($key, $value);
        }

        return $legacyOptions;
    }

    private function mergeOptions(?ConversionOptions $options): ConversionOptions
    {
        if ($this->defaultOptions === null) {
            return $options ?? ConversionOptions::create();
        }

        if ($options === null) {
            return $this->defaultOptions;
        }

        return $this->defaultOptions->merge($options);
    }

    private function validateConversion(InputSource $input, OutputTarget $output): void
    {
        if ($input->isMultiple() && !$output->supportsMultipleFiles()) {
            throw new Exception\ConversionException(
                'Multiple input files require an output directory or compatible output target',
            );
        }

        // Validate file existence for file inputs
        foreach ($input->getFilePaths() as $path) {
            if (!file_exists($path)) {
                throw new Exception\ConversionException("Input file does not exist: {$path}");
            }
        }
    }
}
