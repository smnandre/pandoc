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

/**
 * @author Simon André <smn.andre@gmail.com>
 */
final class Options implements \Countable, \IteratorAggregate
{
    private array $options = [];

    private iterable $input = [];

    private ?string $output = null;

    private ?string $outputDir = null;

    private ?string $format = null;

    public static function create(): self
    {
        return new self();
    }

    public function getInput(): iterable
    {
        return $this->input;
    }

    public function setInput(iterable|string $input): self
    {
        if (is_string($input)) {
            $input = [$input];
        }

        $this->input = $input;

        return $this;
    }

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(string $output): self
    {
        $this->output = $output;

        return $this;
    }

    public function getOutputDir(): ?string
    {
        return $this->outputDir;
    }

    public function setOutputDir(?string $outputDir): self
    {
        $this->outputDir = $outputDir;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function columns(int $columns): self
    {
        return $this->set('--columns', (string) $columns);
    }

    public function count(): int
    {
        return count($this->options);
    }

    public function dataDir(string $dir): self
    {
        return $this->set('--data-dir', $dir);
    }

    public function failIfWarnings(bool $fail = true): self
    {
        return $this->bool('--fail-if-warnings', $fail);
    }

    public function fileScope(bool $fileScope = true): self
    {
        return $this->bool('--file-scope', $fileScope);
    }

    public function from(string $format): self
    {
        return $this->string('--from', $format);
    }

    /**
     * @return \ArrayIterator<string, string>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->toArray());
    }

    public function idPrefix(string $prefix): self
    {
        return $this->string('--id-prefix', $prefix);
    }

    public function input(string $input): self
    {
        return $this->string('-i', $input);
    }

    public function listTables(bool $list = true): self
    {
        return $this->bool('--list-tables', $list);
    }

    public function numberSections(bool $number = true): self
    {
        return $this->bool('--number-sections', $number);
    }

    public function output(string $output): self
    {
        return $this->string('-o', $output);
    }

    public function outputFormat(string $format): self
    {
        return $this->string('-t', $format);
    }

    public function preserveTabs(bool $preserveTabs = true): self
    {
        return $this->bool('--preserve-tabs', $preserveTabs);
    }

    public function referenceLinks(bool $referenceLinks = true): self
    {
        return $this->bool('--reference-links', $referenceLinks);
    }

    public function sandbox(bool $sandbox = true): self
    {
        return $this->bool('--sandbox', $sandbox);
    }

    public function shiftHeadingLevelBy(int $level): self
    {
        return $this->string('--shift-heading-level-by', (string) $level);
    }

    public function standalone(bool $standalone = true): self
    {
        return $this->bool('--standalone', $standalone);
    }

    public function stripComments(bool $strip = true): self
    {
        return $this->bool('--strip-comments', $strip);
    }

    public function tabStop(int $tabStop): self
    {
        return $this->set('--tab-stop', (string) $tabStop);
    }

    public function tableOfContent(bool $toc = true): self
    {
        return $this->bool('--toc', $toc);
    }

    public function titlePrefix(string $string): self
    {
        return $this->string('--title-prefix', $string);
    }

    public function to(string $format): self
    {
        return $this->string('--to', $format);
    }

    public function toc(bool $toc = true): self
    {
        return $this->bool('--toc', $toc);
    }

    public function tocDepth(int $depth): self
    {
        return $this->set('--toc-depth', (string) $depth);
    }

    public function __toString(): string
    {
        return implode(' ', array_map(function (string $name, string $value): string {
            return $name . '=' . $value;
        }, array_keys($this->options), $this->options));
    }

    public function toArray(): array
    {
        $options = $this->options;
        ksort($options, SORT_NATURAL);

        return $options;
    }

    private function bool(string $name, bool $value): self
    {
        if ($value) {
            return $this->set($name, 'true');
        }

        unset($this->options[$name]);

        return $this;
    }

    private function set(string $name, string $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    private function string(string $name, string $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function merge(Options $other): self
    {
        $merged = clone $this;

        // Merge options
        foreach ($other->toArray() as $name => $value) {
            $merged = $merged->set($name, $value); // Use the internal set() to ensure consistency
        }

        // Override input, output, format if set in $other
        if ($other->getInput() !== []) {
            $merged = $merged->setInput($other->getInput());
        }
        if ($other->getOutput() !== null) {
            $merged = $merged->setOutput($other->getOutput());
        }
        if ($other->getOutputDir() !== null) {
            $merged = $merged->setOutputDir($other->getOutputDir());
        }
        if ($other->getFormat() !== null) {
            $merged = $merged->setFormat($other->getFormat());
        }

        return $merged;
    }
}
