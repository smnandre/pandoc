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

use Pandoc\Format\OutputFormat;

/**
 * Represents output target for document conversion.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class OutputTarget
{
    private function __construct(
        private readonly OutputTargetType $type,
        private readonly ?string $target = null,
    ) {}

    /**
     * Create output target for a single file.
     */
    public static function file(string $path): self
    {
        // Ensure parent directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new \RuntimeException("Cannot create output directory: {$dir}");
            }
        }

        return new self(OutputTargetType::FILE, $path);
    }

    /**
     * Create output target for a directory (for multiple file outputs).
     */
    public static function directory(string $dir): self
    {
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new \RuntimeException("Cannot create output directory: {$dir}");
            }
        }

        return new self(OutputTargetType::DIRECTORY, $dir);
    }

    /**
     * Create output target for string return (in-memory).
     */
    public static function string(): self
    {
        return new self(OutputTargetType::STRING, null);
    }

    /**
     * Create output target for stdout.
     */
    public static function stdout(): self
    {
        return new self(OutputTargetType::STDOUT, null);
    }

    /**
     * Create output target for temporary file.
     */
    public static function temporary(string $suffix = '', ?string $dir = null): self
    {
        $tempFile = tempnam($dir ?? sys_get_temp_dir(), 'pandoc_') . $suffix;

        return new self(OutputTargetType::TEMPORARY, $tempFile);
    }

    /**
     * Get the output target type.
     */
    public function getType(): OutputTargetType
    {
        return $this->type;
    }

    /**
     * Get the target data.
     */
    public function getTarget(): string
    {
        if (null === $this->target) {
            throw new \LogicException('Cannot get target for string output.');
        }

        return $this->target;
    }

    /**
     * Generate output file path for input file.
     */
    public function generateOutputPath(string $inputPath, OutputFormat $format): string
    {
        return match ($this->type) {
            OutputTargetType::FILE => $this->getTarget(),
            OutputTargetType::DIRECTORY => $this->generateFileInDirectory($inputPath, $format),
            OutputTargetType::TEMPORARY => $this->getTarget(),
            default => throw new \LogicException('Cannot generate path for this output type'),
        };
    }

    /**
     * Check if this target supports multiple files.
     */
    public function supportsMultipleFiles(): bool
    {
        return match ($this->type) {
            OutputTargetType::DIRECTORY => true,
            default => false,
        };
    }

    /**
     * Check if this target returns content as string.
     */
    public function returnsString(): bool
    {
        return $this->type === OutputTargetType::STRING;
    }

    /**
     * Check if this target outputs to stdout.
     */
    public function isStdout(): bool
    {
        return $this->type === OutputTargetType::STDOUT;
    }

    /**
     * Check if this target is a temporary file.
     */
    public function isTemporary(): bool
    {
        return $this->type === OutputTargetType::TEMPORARY;
    }

    /**
     * Get the output directory path.
     */
    public function getDirectory(): ?string
    {
        return match ($this->type) {
            OutputTargetType::DIRECTORY => $this->getTarget(),
            OutputTargetType::FILE, OutputTargetType::TEMPORARY => dirname($this->getTarget()),
            default => null,
        };
    }

    /**
     * Clean up temporary files.
     */
    public function cleanup(): void
    {
        if ($this->type === OutputTargetType::TEMPORARY && file_exists($this->getTarget())) {
            unlink($this->getTarget());
        }
    }

    private function generateFileInDirectory(string $inputPath, OutputFormat $format): string
    {
        $basename = pathinfo($inputPath, PATHINFO_FILENAME);
        $extension = $format->getExtension();

        return $this->getTarget() . DIRECTORY_SEPARATOR . $basename . '.' . $extension;
    }
}
