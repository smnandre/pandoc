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

use Pandoc\Exception\ConversionFailedException;

/**
 * Unified conversion object representing both configuration and result state.
 * Starts as configuration, becomes result after execution.
 */
final class Conversion
{
    public Options $options;

    /**
     * @param array<string, mixed>|null $config
     */
    public function __construct(
        public ?string $inputPath = null,
        public ?string $inputContent = null,
        public ?string $inputFormat = null,
        public ?string $outputFormat = null,
        public ?string $outputPath = null,
        ?Options $options = null,
        // Legacy support fields
        public ?string $output = null,
        // Execution/result state (populated post-run)
        public ?string $outputContent = null,
        public float $duration = 0,
        public bool $success = false,
        /** @var list<string> */
        public array $warnings = [],
        public ?string $error = null,
        private bool $executed = false,
        // Optional legacy config initialization
        ?array $config = null,
    ) {
        $this->options = $options ?? Options::create();

        if (null !== $config) {
            if (isset($config['inputPath']) && \is_string($config['inputPath'])) {
                $this->inputPath = $config['inputPath'];
            } elseif (isset($config['input']) && \is_string($config['input'])) {
                $this->inputPath = $config['input'];
            }
            if (isset($config['outputPath']) && \is_string($config['outputPath'])) {
                $this->outputPath = $config['outputPath'];
            }
            if (isset($config['inputContent']) && \is_string($config['inputContent'])) {
                $this->inputContent = $config['inputContent'];
            }
            if (isset($config['inputFormat']) && \is_string($config['inputFormat'])) {
                $this->inputFormat = $config['inputFormat'];
            }
            if (isset($config['outputFormat']) && \is_string($config['outputFormat'])) {
                $this->outputFormat = $config['outputFormat'];
            }
            if (isset($config['output']) && \is_string($config['output'])) {
                $this->output = $config['output'];
            }

            // Migrate legacy options to Options object
            if (isset($config['tableOfContents']) && \is_bool($config['tableOfContents'])) {
                $this->options = $this->options->toc($config['tableOfContents']);
            }
            if (isset($config['standalone']) && \is_bool($config['standalone'])) {
                $this->options = $this->options->standalone($config['standalone']);
            }
            if (isset($config['template']) && \is_string($config['template'])) {
                $this->options = $this->options->option('template', $config['template']);
            }
            if (isset($config['variables']) && \is_array($config['variables'])) {
                $vars = [];
                foreach ($config['variables'] as $k => $v) {
                    if (\is_string($k) && \is_string($v)) {
                        $vars[$k] = $v;
                    }
                }
                if ([] !== $vars) {
                    $this->options = $this->options->withVariables($vars);
                }
            }
            if (isset($config['options']) && \is_array($config['options'])) {
                foreach ($config['options'] as $key => $value) {
                    if (\is_string($key)) {
                        $this->options = $this->options->option($key, $value);
                    }
                }
            }
        }
    }

    public function __clone(): void
    {
        $this->options = clone $this->options;
    }

    /**
     * Create new instance with modified property.
     */
    public function with(string $property, mixed $value): self
    {
        $new = clone $this;
        if (property_exists($new, $property)) {
            $new->$property = $value;
        }

        return $new;
    }

    /**
     * Set variable for template processing.
     */
    public function withVariable(string $key, string $value): self
    {
        $new = clone $this;
        $new->options = $new->options->variable($key, $value);

        return $new;
    }

    /**
     * Set custom pandoc option.
     */
    public function withOption(string $option, mixed $value = true): self
    {
        $new = clone $this;
        $new->options = $new->options->option($option, $value);

        return $new;
    }

    /**
     * Convert configuration to pandoc command arguments.
     *
     * @return list<string>
     */
    public function toCommandArgs(): array
    {
        $args = [];

        if ($this->inputFormat) {
            $args[] = "--from={$this->inputFormat}";
        }

        if ($this->outputFormat) {
            $args[] = "--to={$this->outputFormat}";
        }

        if ($this->outputPath) {
            $args[] = "--output={$this->outputPath}";
        }

        $optionsArray = $this->options->toArray();

        foreach ($optionsArray['options'] as $option => $value) {
            if (true === $value) {
                $args[] = "--{$option}";
            } elseif (false !== $value && null !== $value) {
                $valueString = \is_scalar($value) ? (string) $value : '';
                if ('' !== $valueString) {
                    $args[] = "--{$option}={$valueString}";
                }
            }
        }

        foreach ($optionsArray['variables'] as $key => $value) {
            $args[] = "--variable={$key}:{$value}";
        }

        foreach ($optionsArray['metadata'] as $key => $value) {
            $args[] = "--metadata={$key}:{$value}";
        }

        if ($this->inputPath && !$this->isStringInput()) {
            $args[] = $this->inputPath;
        }

        return $args;
    }

    /**
     * Mark conversion as executed with results.
     *
     * @param list<string> $warnings
     */
    public function markExecuted(
        bool $success,
        float $duration,
        ?string $outputPath = null,
        ?string $outputContent = null,
        ?string $error = null,
        array $warnings = [],
    ): self {
        $this->executed = true;
        $this->success = $success;
        $this->duration = $duration;
        $this->outputPath = $outputPath ?? $this->outputPath;
        $this->outputContent = $outputContent;
        $this->error = $error;
        $this->warnings = $warnings;

        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->executed && $this->success;
    }

    public function getContent(): string
    {
        if (!$this->executed) {
            throw new \RuntimeException('Conversion not executed yet');
        }

        if (!$this->success) {
            throw new ConversionFailedException('Conversion failed: '.$this->error);
        }

        return $this->outputContent ?? '';
    }

    public function getPath(): ?string
    {
        return $this->outputPath;
    }

    public function getDuration(): float
    {
        return $this->duration;
    }

    public function getOutputPath(): string
    {
        if (!$this->executed) {
            throw new \RuntimeException('Conversion not executed yet');
        }

        if (!$this->success) {
            throw new \RuntimeException('Conversion failed: '.$this->error);
        }

        return $this->outputPath ?? $this->output ?? '';
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @return list<string>
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function isConfigured(): bool
    {
        return ($this->inputPath || $this->inputContent)
               && ($this->outputPath || $this->output || $this->isStringOutput());
    }

    public function isExecuted(): bool
    {
        return $this->executed;
    }

    public function isStringInput(): bool
    {
        return null !== $this->inputContent;
    }

    public function isStringOutput(): bool
    {
        return null === $this->outputPath && null === $this->output;
    }

    public function detectInputFormat(): ?string
    {
        if ($this->inputFormat) {
            return $this->inputFormat;
        }

        if ($this->inputPath) {
            $extension = strtolower(pathinfo($this->inputPath, \PATHINFO_EXTENSION));

            return match ($extension) {
                'md', 'markdown' => 'markdown',
                'html', 'htm' => 'html',
                'docx' => 'docx',
                'odt' => 'odt',
                'tex' => 'latex',
                'rst' => 'rst',
                'txt' => 'plain',
                default => null,
            };
        }

        return null;
    }

    public function detectOutputFormat(): ?string
    {
        if ($this->outputFormat) {
            return $this->outputFormat;
        }

        $output = $this->outputPath ?? $this->output;
        if ($output && !$this->isStringOutput()) {
            $extension = strtolower(pathinfo($output, \PATHINFO_EXTENSION));

            return match ($extension) {
                'html', 'htm' => 'html',
                'pdf' => 'pdf',
                'docx' => 'docx',
                'odt' => 'odt',
                'tex' => 'latex',
                'md', 'markdown' => 'markdown',
                'rst' => 'rst',
                'txt' => 'plain',
                'epub' => 'epub',
                default => null,
            };
        }

        return null;
    }

    public function getInputContent(): ?string
    {
        return $this->inputContent;
    }

    /**
     * Explicit input path accessor (for symmetry with getInputContent()).
     */
    public function getInputPath(): ?string
    {
        return $this->inputPath;
    }

    public function validate(): void
    {
        if (!$this->isConfigured()) {
            throw new \InvalidArgumentException('Conversion not properly configured: missing input or output');
        }

        if ($this->inputPath && !$this->isStringInput() && !file_exists($this->inputPath)) {
            throw new \InvalidArgumentException("Input file not found: {$this->inputPath}");
        }

        $output = $this->outputPath ?? $this->output;
        if ($output && !$this->isStringOutput()) {
            $outputDir = \dirname($output);
            if (!is_dir($outputDir)) {
                throw new \InvalidArgumentException("Output directory not found: {$outputDir}");
            }
        }
    }
}
