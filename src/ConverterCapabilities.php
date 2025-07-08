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

use Pandoc\Converter\ConverterInterface;
use Pandoc\Format\InputFormat;
use Pandoc\Format\OutputFormat;

/**
 * Provides information about converter capabilities.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class ConverterCapabilities
{
    public function __construct(
        private readonly ConverterInterface $converter,
    ) {}

    /**
     * Get Pandoc version information.
     */
    public function getPandocInfo(): PandocInfo
    {
        return $this->converter->getPandocInfo();
    }

    /**
     * Get supported input formats.
     *
     * @return array<string>
     */
    public function getSupportedInputFormats(): array
    {
        return $this->converter->listInputFormats();
    }

    /**
     * Get supported output formats.
     *
     * @return array<string>
     */
    public function getSupportedOutputFormats(): array
    {
        return $this->converter->listOutputFormats();
    }

    /**
     * Get available highlight languages.
     *
     * @return array<string>
     */
    public function getHighlightLanguages(): array
    {
        return $this->converter->listHighlightLanguages();
    }

    /**
     * Get available highlight styles.
     *
     * @return array<string>
     */
    public function getHighlightStyles(): array
    {
        return $this->converter->listHighlightStyles();
    }

    /**
     * Check if an input format is supported.
     */
    public function supportsInputFormat(InputFormat $format): bool
    {
        return in_array($format->value, $this->getSupportedInputFormats(), true);
    }

    /**
     * Check if an output format is supported.
     */
    public function supportsOutputFormat(OutputFormat $format): bool
    {
        return in_array($format->value, $this->getSupportedOutputFormats(), true);
    }

    /**
     * Check if a conversion is supported.
     */
    public function supportsConversion(InputFormat $input, OutputFormat $output): bool
    {
        return $this->supportsInputFormat($input) && $this->supportsOutputFormat($output);
    }

    /**
     * Get input formats that support the given file extension.
     *
     * @return array<InputFormat>
     */
    public function getInputFormatsForExtension(string $extension): array
    {
        $formats = [];

        foreach (InputFormat::cases() as $format) {
            if ($format->supportsExtension($extension) && $this->supportsInputFormat($format)) {
                $formats[] = $format;
            }
        }

        return $formats;
    }

    /**
     * Get output formats that can generate the given file extension.
     *
     * @return array<OutputFormat>
     */
    public function getOutputFormatsForExtension(string $extension): array
    {
        $formats = [];

        foreach (OutputFormat::cases() as $format) {
            if ($format->getExtension() === ltrim($extension, '.') && $this->supportsOutputFormat($format)) {
                $formats[] = $format;
            }
        }

        return $formats;
    }

    /**
     * Check if LaTeX is available for PDF generation.
     */
    public function hasLatexSupport(): bool
    {
        // This could be enhanced to actually check for LaTeX availability
        return $this->supportsOutputFormat(OutputFormat::PDF);
    }

    /**
     * Get a summary of capabilities.
     */
    public function getSummary(): array
    {
        return [
            'pandoc_version' => $this->getPandocInfo()->getVersion(),
            'pandoc_path' => $this->getPandocInfo()->getPath(),
            'input_formats_count' => count($this->getSupportedInputFormats()),
            'output_formats_count' => count($this->getSupportedOutputFormats()),
            'highlight_languages_count' => count($this->getHighlightLanguages()),
            'highlight_styles_count' => count($this->getHighlightStyles()),
            'pdf_support' => $this->hasLatexSupport(),
        ];
    }
}
