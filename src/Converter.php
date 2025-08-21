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
 * Main converter implementation.
 */
final class Converter implements ConverterInterface
{
    private ?string $inputPath = null;
    private ?string $inputContent = null;
    private ?string $inputFormat = null;
    private ?string $outputFormat = null;
    private ?string $output = null;
    private Options $options;

    private BinaryInterface $binary;

    public function __construct(?BinaryInterface $binary = null)
    {
        $this->binary = $binary ?? PandocBinary::create();
        $this->options = Options::create();
    }

    public static function create(?BinaryInterface $binary = null): self
    {
        return new self($binary);
    }

    public function input(string $contentOrPath): self
    {
        return $this->file($contentOrPath);
    }

    public function content(string $content): self
    {
        $clone = clone $this;
        $clone->inputContent = $content;
        $clone->inputPath = null;

        return $clone;
    }

    public function file(string $filename): self
    {
        $clone = clone $this;
        $clone->inputPath = $filename;
        $clone->inputContent = null;

        return $clone;
    }

    public function from(?string $format): self
    {
        $clone = clone $this;
        $clone->inputFormat = $format;

        return $clone;
    }

    public function to(?string $format): self
    {
        $clone = clone $this;
        $clone->outputFormat = $format;

        return $clone;
    }

    public function output(?string $fileOrDir): self
    {
        $clone = clone $this;
        $clone->output = $fileOrDir;

        return $clone;
    }

    /**
     * @param Options|array<string, mixed> $options
     */
    public function options(Options|array $options): self
    {
        $clone = clone $this;

        if ($options instanceof Options) {
            $clone->options = $clone->options->merge($options);
        } else {
            foreach ($options as $key => $value) {
                $clone->options = $clone->options->option($key, $value);
            }
        }

        return $clone;
    }

    public function option(string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->options = $clone->options->option($key, $value);

        return $clone;
    }

    public function variable(string $key, string $value): self
    {
        $clone = clone $this;
        $clone->options = $clone->options->variable($key, $value);

        return $clone;
    }

    /**
     * @param array<string, string> $variables
     */
    public function variables(array $variables): self
    {
        $clone = clone $this;
        $clone->options = $clone->options->withVariables($variables);

        return $clone;
    }

    public function metadata(string $key, string $value): self
    {
        $clone = clone $this;
        $clone->options = $clone->options->metadata($key, $value);

        return $clone;
    }

    /**
     * @param array<string, string> $metadata
     */
    public function metadatas(array $metadata): self
    {
        $clone = clone $this;
        $clone->options = $clone->options->withMetadata($metadata);

        return $clone;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function with(array $options): self
    {
        return $this->options($options);
    }

    public function convert(): Conversion
    {
        $conversion = $this->resolveToConversion();

        return $this->binary->convert($conversion);
    }

    public function getContent(): string
    {
        return $this->convert()->getContent();
    }

    public function getPath(): ?string
    {
        return $this->convert()->getPath();
    }

    public function fresh(): self
    {
        return new self($this->binary);
    }

    public function __clone(): void
    {
        $this->options = clone $this->options;
    }

    /**
     * Apply guesses and create final Conversion object.
     */
    private function resolveToConversion(): Conversion
    {
        if (null === $this->inputPath && null === $this->inputContent) {
            throw new Exception\InvalidArgumentException('Input must be set before conversion');
        }

        $resolvedInputFormat = $this->inputFormat ?? $this->guessInputFormat();

        $resolvedOutputFormat = $this->outputFormat ?? $this->guessOutputFormat();

        if (!$resolvedOutputFormat) {
            throw new Exception\InvalidArgumentException('Output format must be specified or guessable from output file extension');
        }

        $resolvedOutput = $this->resolveOutputPath();

        $conversion = new Conversion(
            inputPath: $this->inputPath,
            inputContent: $this->inputContent,
            inputFormat: $resolvedInputFormat,
            outputFormat: $resolvedOutputFormat,
            outputPath: $resolvedOutput,
            options: $this->options
        );

        return $conversion;
    }

    private function guessInputFormat(): ?string
    {
        if (null !== $this->inputPath) {
            return Format::fromFile($this->inputPath)?->value;
        }

        $input = $this->inputContent ?? '';
        if ('' !== $input && (str_contains($input, '#') || str_contains($input, '**') || str_contains($input, '_'))) {
            return 'markdown';
        }

        if ('' !== $input && str_contains($input, '<') && str_contains($input, '>')) {
            return 'html';
        }

        return null;
    }

    private function guessOutputFormat(): ?string
    {
        if ($this->output) {
            return Format::fromFile($this->output)?->value;
        }

        return null;
    }

    private function resolveOutputPath(): ?string
    {
        if (!$this->output) {
            return null;
        }

        if (is_dir($this->output)) {
            $baseName = $this->getInputBaseName();
            $extension = Format::tryFrom($this->outputFormat ?? 'html')?->getExtension() ?? 'html';

            return rtrim($this->output, '/').'/'.$baseName.'.'.$extension;
        }

        return $this->output;
    }

    private function getInputBaseName(): string
    {
        if (null !== $this->inputPath && file_exists($this->inputPath)) {
            return pathinfo($this->inputPath, \PATHINFO_FILENAME);
        }

        return 'document';
    }
}
