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

/**
 * Static facade for convenience - delegates to injected or default converter.
 */
final class Pandoc
{
    private static ?ConverterInterface $sharedConverter = null;
    private static ?BinaryInterface $defaultBinary = null;

    /**
     * Quick string conversion.
     */
    /**
     * @param array<string, mixed> $options
     */
    public static function convert(
        string $input,
        string $format,
        array $options = [],
    ): string {
        return self::getConverter()
            ->content($input)
            ->to($format)
            ->options($options)
            ->getContent();
    }

    /**
     * Quick file conversion.
     */
    /**
     * @param array<string, mixed> $options
     */
    public static function convertFile(
        string $inputFile,
        string $outputFile,
        array $options = [],
    ): Conversion {
        return self::getConverter()
            ->file($inputFile)
            ->output($outputFile)
            ->options($options)
            ->convert();
    }

    /**
     * Start with input.
     */
    public static function input(string $input): ConverterInterface
    {
        return self::getConverter()->input($input);
    }

    /**
     * Start with explicit content.
     */
    public static function content(string $input): ConverterInterface
    {
        return self::getConverter()->content($input);
    }

    /**
     * Start with explicit input file.
     */
    public static function file(string $filename): ConverterInterface
    {
        return self::getConverter()->file($filename);
    }

    public static function to(string $format): ConverterInterface
    {
        return self::getConverter()->to($format);
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function with(array $options): ConverterInterface
    {
        return self::getConverter()->options($options);
    }

    /**
     * Get fresh converter instance.
     */
    public static function converter(?BinaryInterface $binary = null): ConverterInterface
    {
        return Converter::create($binary ?? self::$defaultBinary);
    }

    /**
     * Create options object.
     *
     * @param array<string, mixed> $options
     */
    public static function options(array $options = []): Options
    {
        return Options::create($options);
    }

    public static function setConverter(ConverterInterface $converter): void
    {
        self::$sharedConverter = $converter;
    }

    public static function resetConverter(): void
    {
        self::$sharedConverter = null;
    }

    public static function setDefaultBinary(BinaryInterface $binary): void
    {
        self::$defaultBinary = $binary;
    }

    public static function resetDefaultBinary(): void
    {
        self::$defaultBinary = null;
    }

    private static function getConverter(): ConverterInterface
    {
        return self::$sharedConverter ?? Converter::create(self::$defaultBinary);
    }

    private static function getBinary(): BinaryInterface
    {
        if (null !== self::$defaultBinary) {
            return self::$defaultBinary;
        }

        return self::$defaultBinary = PandocBinary::create();
    }

    public static function isInstalled(): bool
    {
        try {
            if (self::$defaultBinary instanceof BinaryInterface) {
                self::$defaultBinary->getVersion();

                return true;
            }

            return PandocBinary::isInstalled();
        } catch (\Exception) {
            return false;
        }
    }

    public static function version(): string
    {
        return self::getBinary()->getVersion();
    }

    /**
     * @return list<string>
     */
    public static function inputFormats(): array
    {
        return self::getBinary()->getInputFormats();
    }

    /**
     * @return list<string>
     */
    public static function outputFormats(): array
    {
        return self::getBinary()->getOutputFormats();
    }

    public static function supports(string $from, string $to): bool
    {
        return self::getBinary()->supports($from, $to);
    }

    public static function canConvertFile(string $inputFile, string $outputFile): bool
    {
        try {
            if (!file_exists($inputFile)) {
                return false;
            }

            $inputFormat = Format::fromFile($inputFile);
            $outputFormat = Format::fromFile($outputFile);

            if (!$inputFormat || !$outputFormat) {
                return false;
            }

            return self::supports($inputFormat->value, $outputFormat->value);
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * @return list<Format>
     */
    public static function getSuggestedFormats(string $inputFile): array
    {
        $inputFormat = Format::fromFile($inputFile);

        if (!$inputFormat) {
            return [];
        }

        $supportedFormats = [];
        foreach (Format::outputFormats() as $format) {
            if (self::supports($inputFormat->value, $format->value)) {
                $supportedFormats[] = $format;
            }
        }

        return $supportedFormats;
    }

    public static function markdownToHtml(string $markdown): string
    {
        return self::content($markdown)->from('gfm')->to('html')->getContent();
    }

    public static function htmlToMarkdown(string $html): string
    {
        return self::content($html)->from('html')->to('markdown')->getContent();
    }

    /**
     * Convert multiple files to directory.
     *
     * @param list<string> $inputFiles
     *
     * @return list<Conversion>
     */
    public static function convertFiles(array $inputFiles, string $outputDir, string $format): array
    {
        $results = [];

        foreach ($inputFiles as $inputFile) {
            $result = self::getConverter()
                ->file($inputFile)
                ->to($format)
                ->output($outputDir)
                ->convert();
            $results[] = $result;
        }

        return $results;
    }
}
