<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Result;

/**
 * Result of a document conversion operation.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class ConversionResult
{
    /**
     * @param array<string> $outputPaths
     * @param array<string> $warnings
     */
    public function __construct(
        private readonly array $outputPaths = [],
        private readonly ?string $content = null,
        private readonly ?DocumentMetadata $metadata = null,
        private readonly float $duration = 0.0,
        private readonly array $warnings = [],
    ) {}

    /**
     * Get list of output file paths created.
     *
     * @return array<string>
     */
    public function getOutputPaths(): array
    {
        return $this->outputPaths;
    }

    /**
     * Get the first output path (for single file conversions).
     */
    public function getOutputPath(): ?string
    {
        return $this->outputPaths[0] ?? null;
    }

    /**
     * Get content for string outputs.
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Get document metadata if available.
     */
    public function getMetadata(): ?DocumentMetadata
    {
        return $this->metadata;
    }

    /**
     * Get conversion duration in seconds.
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * Get any warnings generated during conversion.
     *
     * @return array<string>
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Check if conversion was successful.
     */
    public function isSuccessful(): bool
    {
        return !empty($this->outputPaths) || $this->content !== null;
    }

    /**
     * Check if there were any warnings.
     */
    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * Check if this is a string result.
     */
    public function isStringResult(): bool
    {
        return $this->content !== null;
    }

    /**
     * Check if this is a file result.
     */
    public function isFileResult(): bool
    {
        return !empty($this->outputPaths);
    }

    /**
     * Count number of output files created.
     */
    public function getOutputCount(): int
    {
        return count($this->outputPaths);
    }

    /**
     * Create a successful file conversion result.
     *
     * @param array<string>|string $outputPaths
     * @param array<string> $warnings
     */
    public static function fileResult(
        array|string $outputPaths,
        ?DocumentMetadata $metadata = null,
        float $duration = 0.0,
        array $warnings = [],
    ): self {
        $paths = is_string($outputPaths) ? [$outputPaths] : $outputPaths;

        return new self($paths, null, $metadata, $duration, $warnings);
    }

    /**
     * Create a successful string conversion result.
     *
     * @param array<string> $warnings
     */
    public static function stringResult(
        string $content,
        ?DocumentMetadata $metadata = null,
        float $duration = 0.0,
        array $warnings = [],
    ): self {
        return new self([], $content, $metadata, $duration, $warnings);
    }

    /**
     * Create a new result with additional warnings.
     *
     * @param array<string> $warnings
     */
    public function withWarnings(array $warnings): self
    {
        return new self(
            $this->outputPaths,
            $this->content,
            $this->metadata,
            $this->duration,
            array_merge($this->warnings, $warnings),
        );
    }

    /**
     * Create a new result with updated metadata.
     */
    public function withMetadata(DocumentMetadata $metadata): self
    {
        return new self(
            $this->outputPaths,
            $this->content,
            $metadata,
            $this->duration,
            $this->warnings,
        );
    }

    /**
     * Get summary of the conversion result.
     */
    public function getSummary(): string
    {
        $parts = [];

        if ($this->isStringResult()) {
            $length = strlen($this->content ?? '');
            $parts[] = "Generated {$length} characters";
        } elseif ($this->isFileResult()) {
            $count = count($this->outputPaths);
            $parts[] = "Generated {$count} file" . ($count > 1 ? 's' : '');
        }

        if ($this->duration > 0) {
            $parts[] = sprintf("in %.3fs", $this->duration);
        }

        if ($this->hasWarnings()) {
            $count = count($this->warnings);
            $parts[] = "with {$count} warning" . ($count > 1 ? 's' : '');
        }

        return implode(' ', $parts) ?: 'Conversion completed';
    }
}
