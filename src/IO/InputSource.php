<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\IO;

use Pandoc\Format\InputFormat;
use Symfony\Component\Finder\Finder;

/**
 * Represents input source for document conversion.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class InputSource
{
    private function __construct(
        private readonly InputSourceType $type,
        private readonly mixed $source,
        private readonly ?InputFormat $format = null,
    ) {}

    /**
     * Create input source from a single file.
     */
    public static function file(string $path, ?InputFormat $format = null): self
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Input file does not exist: {$path}");
        }

        return new self(InputSourceType::FILE, $path, $format);
    }

    /**
     * Create input source from multiple files.
     *
     * @param array<string> $paths
     */
    public static function files(array $paths, ?InputFormat $format = null): self
    {
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                throw new \InvalidArgumentException("Input file does not exist: {$path}");
            }
        }

        return new self(InputSourceType::FILES, $paths, $format);
    }

    /**
     * Create input source from Symfony Finder.
     */
    public static function finder(Finder $finder, ?InputFormat $format = null): self
    {
        return new self(InputSourceType::FINDER, $finder, $format);
    }

    /**
     * Create input source from string content.
     */
    public static function string(string $content, ?InputFormat $format = null): self
    {
        return new self(InputSourceType::STRING, $content, $format);
    }

    /**
     * Create input source from stdin.
     */
    public static function stdin(?InputFormat $format = null): self
    {
        return new self(InputSourceType::STDIN, null, $format);
    }

    /**
     * Get the input source type.
     */
    public function getType(): InputSourceType
    {
        return $this->type;
    }

    /**
     * Get the source data.
     */
    public function getSource(): mixed
    {
        return $this->source;
    }

    /**
     * Get the specified input format.
     */
    public function getFormat(): ?InputFormat
    {
        return $this->format;
    }

    /**
     * Get input format with auto-detection if not specified.
     */
    public function getFormatOrDetect(): ?InputFormat
    {
        if ($this->format !== null) {
            return $this->format;
        }

        // Try to auto-detect format from file extension
        switch ($this->type) {
            case InputSourceType::FILE:
                return InputFormat::fromExtension(pathinfo($this->source, PATHINFO_EXTENSION));

            case InputSourceType::FILES:
                if (!empty($this->source)) {
                    return InputFormat::fromExtension(pathinfo($this->source[0], PATHINFO_EXTENSION));
                }
                break;

            case InputSourceType::FINDER:
                foreach ($this->source as $file) {
                    return InputFormat::fromExtension($file->getExtension());
                }
                break;
        }

        return null;
    }

    /**
     * Set or override the input format.
     */
    public function withFormat(InputFormat $format): self
    {
        return new self($this->type, $this->source, $format);
    }

    /**
     * Check if this input source contains multiple files.
     */
    public function isMultiple(): bool
    {
        return match ($this->type) {
            InputSourceType::FILES => count($this->source) > 1,
            InputSourceType::FINDER => count($this->source) > 1,
            default => false,
        };
    }

    /**
     * Get all file paths from this input source.
     *
     * @return array<string>
     */
    public function getFilePaths(): array
    {
        return match ($this->type) {
            InputSourceType::FILE => [$this->source],
            InputSourceType::FILES => $this->source,
            InputSourceType::FINDER => array_map(
                fn(\SplFileInfo $file) => $file->getRealPath(),
                iterator_to_array($this->source)
            ),
            default => [],
        };
    }

    /**
     * Get string content (for string and file inputs).
     */
    public function getContent(): ?string
    {
        return match ($this->type) {
            InputSourceType::STRING => $this->source,
            InputSourceType::FILE => file_get_contents($this->source),
            InputSourceType::STDIN => file_get_contents('php://stdin'),
            default => null,
        };
    }

    /**
     * Count the number of input items.
     */
    public function count(): int
    {
        return match ($this->type) {
            InputSourceType::FILE, InputSourceType::STRING, InputSourceType::STDIN => 1,
            InputSourceType::FILES => count($this->source),
            InputSourceType::FINDER => count($this->source),
        };
    }
}