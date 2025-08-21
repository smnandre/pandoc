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
 * Options class for Pandoc conversion supporting options, variables, and metadata.
 *
 * @implements \IteratorAggregate<string, mixed>
 */
final class Options implements \Countable, \IteratorAggregate
{
    /** @var array<string, mixed> */
    private array $options;
    /** @var array<string, string> */
    private array $variables;
    /** @var array<string, string> */
    private array $metadata;

    /**
     * @param array<string, mixed> $options
     */
    public static function create(array $options = []): self
    {
        return new self($options);
    }

    /**
     * @param array<string, mixed>  $options
     * @param array<string, string> $variables
     * @param array<string, string> $metadata
     */
    public function __construct(array $options = [], array $variables = [], array $metadata = [])
    {
        $this->options = $options;
        $this->variables = $variables;
        $this->metadata = $metadata;
    }

    public function option(string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->options[$key] = $value;

        return $clone;
    }

    public function columns(int $columns): self
    {
        return $this->option('columns', $columns);
    }

    public function dataDir(string $dir): self
    {
        return $this->option('data-dir', $dir);
    }

    public function failIfWarnings(bool $fail = true): self
    {
        return $this->option('fail-if-warnings', $fail);
    }

    public function fileScope(bool $fileScope = true): self
    {
        return $this->option('file-scope', $fileScope);
    }

    public function idPrefix(string $prefix): self
    {
        return $this->option('id-prefix', $prefix);
    }

    public function numberSections(bool $number = true): self
    {
        return $this->option('number-sections', $number);
    }

    public function preserveTabs(bool $preserveTabs = true): self
    {
        return $this->option('preserve-tabs', $preserveTabs);
    }

    public function referenceLinks(bool $referenceLinks = true): self
    {
        return $this->option('reference-links', $referenceLinks);
    }

    public function sandbox(bool $sandbox = true): self
    {
        return $this->option('sandbox', $sandbox);
    }

    public function shiftHeadingLevelBy(int $level): self
    {
        return $this->option('shift-heading-level-by', $level);
    }

    public function standalone(bool $standalone = true): self
    {
        return $this->option('standalone', $standalone);
    }

    public function stripComments(bool $strip = true): self
    {
        return $this->option('strip-comments', $strip);
    }

    public function tabStop(int $tabStop): self
    {
        return $this->option('tab-stop', $tabStop);
    }

    public function tableOfContent(bool $toc = true): self
    {
        return $this->option('toc', $toc);
    }

    public function toc(bool $toc = true): self
    {
        return $this->option('toc', $toc);
    }

    public function tocDepth(int $depth): self
    {
        return $this->option('toc-depth', $depth);
    }

    public function titlePrefix(string $string): self
    {
        return $this->option('title-prefix', $string);
    }

    public function variable(string $key, string $value): self
    {
        $clone = clone $this;
        $clone->variables[$key] = $value;

        return $clone;
    }

    /**
     * @param array<string, string> $variables
     */
    public function withVariables(array $variables): self
    {
        $clone = clone $this;
        $clone->variables = array_merge($clone->variables, $variables);

        return $clone;
    }

    public function metadata(string $key, string $value): self
    {
        $clone = clone $this;
        $clone->metadata[$key] = $value;

        return $clone;
    }

    /**
     * @param array<string, string> $metadata
     */
    public function withMetadata(array $metadata): self
    {
        $clone = clone $this;
        $clone->metadata = array_merge($clone->metadata, $metadata);

        return $clone;
    }

    public function __toString(): string
    {
        $parts = [];

        foreach ($this->options as $name => $value) {
            if (\is_bool($value)) {
                if ($value) {
                    $parts[] = "--{$name}";
                }
            } else {
                if (\is_scalar($value) || (\is_object($value) && method_exists($value, '__toString'))) {
                    $parts[] = "--{$name}=".(string) $value;
                }
            }
        }

        foreach ($this->variables as $key => $value) {
            $parts[] = "--variable={$key}:{$value}";
        }

        foreach ($this->metadata as $key => $value) {
            $parts[] = "--metadata={$key}:{$value}";
        }

        return implode(' ', $parts);
    }

    /**
     * Return structured array with options, variables, and metadata.
     *
     * @return array{options: array<string, mixed>, variables: array<string, string>, metadata: array<string, string>}
     */
    public function toArray(): array
    {
        return [
            'options' => $this->options,
            'variables' => $this->variables,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Get only the options (for backward compatibility).
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get only the variables.
     *
     * @return array<string, string>
     */
    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * Get only the metadata.
     *
     * @return array<string, string>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @return \Traversable<string, mixed>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->options);
    }

    public function count(): int
    {
        return \count($this->options) + \count($this->variables) + \count($this->metadata);
    }

    public function merge(self $other): self
    {
        $otherArray = $other->toArray();

        return new self(
            array_merge($this->options, $otherArray['options']),
            array_merge($this->variables, $otherArray['variables']),
            array_merge($this->metadata, $otherArray['metadata'])
        );
    }
}
