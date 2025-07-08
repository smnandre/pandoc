<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Configuration;

/**
 * Configuration options for Pandoc document conversion.
 *
 * This class provides a fluent interface for configuring Pandoc options
 * without mixing I/O concerns with conversion settings.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class ConversionOptions implements \Countable, \IteratorAggregate
{
    /**
     * @var array<string, string>
     */
    private array $options = [];

    /**
     * @var array<string, string>
     */
    private array $variables = [];

    /**
     * @var array<string>
     */
    private array $metadataFiles = [];

    public static function create(): self
    {
        return new self();
    }

    // Table of Contents
    public function tableOfContents(bool $enabled = true): self
    {
        return $this->bool('--toc', $enabled);
    }

    public function tocDepth(int $depth): self
    {
        return $this->string('--toc-depth', (string) $depth);
    }

    // Sectioning
    public function numberSections(bool $enabled = true): self
    {
        return $this->bool('--number-sections', $enabled);
    }

    public function shiftHeadingLevelBy(int $levels): self
    {
        return $this->string('--shift-heading-level-by', (string) $levels);
    }

    public function sectionDivs(bool $enabled = true): self
    {
        return $this->bool('--section-divs', $enabled);
    }

    // Document structure
    public function standalone(bool $enabled = true): self
    {
        return $this->bool('--standalone', $enabled);
    }

    public function template(string $template): self
    {
        return $this->string('--template', $template);
    }

    public function metadataFile(string $file): self
    {
        $this->metadataFiles[] = $file;
        return $this;
    }

    public function variable(string $name, string $value): self
    {
        $this->variables[$name] = $value;
        return $this;
    }

    // Content processing
    public function preserveTabs(bool $enabled = true): self
    {
        return $this->bool('--preserve-tabs', $enabled);
    }

    public function tabStop(int $spaces): self
    {
        return $this->string('--tab-stop', (string) $spaces);
    }

    public function columns(int $columns): self
    {
        return $this->string('--columns', (string) $columns);
    }

    public function stripComments(bool $enabled = true): self
    {
        return $this->bool('--strip-comments', $enabled);
    }

    public function fileScope(bool $enabled = true): self
    {
        return $this->bool('--file-scope', $enabled);
    }

    // Links and references
    public function referenceLinks(bool $enabled = true): self
    {
        return $this->bool('--reference-links', $enabled);
    }

    public function referenceLocation(string $location): self
    {
        return $this->string('--reference-location', $location);
    }

    public function idPrefix(string $prefix): self
    {
        return $this->string('--id-prefix', $prefix);
    }

    // Citation and bibliography
    public function citeproc(bool $enabled = true): self
    {
        return $this->bool('--citeproc', $enabled);
    }

    public function bibliography(string $file): self
    {
        return $this->string('--bibliography', $file);
    }

    public function csl(string $file): self
    {
        return $this->string('--csl', $file);
    }

    public function citationAbbreviations(string $file): self
    {
        return $this->string('--citation-abbreviations', $file);
    }

    // Math rendering
    public function mathml(bool $enabled = true): self
    {
        return $this->bool('--mathml', $enabled);
    }

    public function webtex(string $url = ''): self
    {
        return $this->string('--webtex', $url);
    }

    public function mathjax(string $url = ''): self
    {
        return $this->string('--mathjax', $url);
    }

    public function katex(string $url = ''): self
    {
        return $this->string('--katex', $url);
    }

    // Code highlighting
    public function highlight(bool $enabled = true): self
    {
        return $this->bool('--highlight-style', $enabled ? 'pygments' : 'null');
    }

    public function highlightStyle(string $style): self
    {
        return $this->string('--highlight-style', $style);
    }

    public function syntaxDefinition(string $file): self
    {
        return $this->string('--syntax-definition', $file);
    }

    // HTML specific
    public function htmlQTags(bool $enabled = true): self
    {
        return $this->bool('--html-q-tags', $enabled);
    }

    public function emailObfuscation(string $method): self
    {
        return $this->string('--email-obfuscation', $method);
    }

    // LaTeX specific
    public function latexEngine(string $engine): self
    {
        return $this->string('--pdf-engine', $engine);
    }

    public function latexEngineOpt(string $option): self
    {
        return $this->string('--pdf-engine-opt', $option);
    }

    // Error handling
    public function failIfWarnings(bool $enabled = true): self
    {
        return $this->bool('--fail-if-warnings', $enabled);
    }

    public function sandbox(bool $enabled = true): self
    {
        return $this->bool('--sandbox', $enabled);
    }

    // Configuration
    public function dataDir(string $dir): self
    {
        return $this->string('--data-dir', $dir);
    }

    // Advanced
    public function filter(string $filter): self
    {
        return $this->string('--filter', $filter);
    }

    public function luaFilter(string $filter): self
    {
        return $this->string('--lua-filter', $filter);
    }

    public function include(string $type, string $file): self
    {
        return match ($type) {
            'before-body' => $this->string('--include-before-body', $file),
            'after-body' => $this->string('--include-after-body', $file),
            'in-header' => $this->string('--include-in-header', $file),
            default => throw new \InvalidArgumentException("Invalid include type: {$type}"),
        };
    }

    // Fluent option setters
    private function bool(string $option, bool $value): self
    {
        $clone = clone $this;

        if ($value) {
            $clone->options[$option] = 'true';
        } else {
            unset($clone->options[$option]);
        }

        return $clone;
    }

    private function string(string $option, string $value): self
    {
        $clone = clone $this;
        $clone->options[$option] = $value;
        return $clone;
    }

    // Access methods
    public function toArray(): array
    {
        return $this->options;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getMetadataFiles(): array
    {
        return $this->metadataFiles;
    }

    public function count(): int
    {
        return count($this->options);
    }

    /**
     * @return \ArrayIterator<string, string>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->toArray());
    }

    public function __toString(): string
    {
        $args = [];

        foreach ($this->options as $name => $value) {
            if ($value === 'true') {
                $args[] = $name;
            } else {
                $args[] = $name . '=' . escapeshellarg($value);
            }
        }

        // Add variables
        foreach ($this->variables as $name => $value) {
            $args[] = '--variable=' . escapeshellarg($name . ':' . $value);
        }

        // Add metadata files
        foreach ($this->metadataFiles as $file) {
            $args[] = '--metadata-file=' . escapeshellarg($file);
        }

        return implode(' ', $args);
    }

    /**
     * Merge with another ConversionOptions instance.
     */
    public function merge(ConversionOptions $other): self
    {
        $merged = clone $this;

        foreach ($other->options as $key => $value) {
            $merged->options[$key] = $value;
        }

        foreach ($other->variables as $key => $value) {
            $merged->variables[$key] = $value;
        }

        $merged->metadataFiles = array_merge($merged->metadataFiles, $other->metadataFiles);

        return $merged;
    }
}
