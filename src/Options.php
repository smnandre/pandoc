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

use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<string, string>
 */
final class Options implements Countable, IteratorAggregate
{
    /**
     * @var array<string, string>
     */
    private array $options = [];

    public static function create(): self
    {
        return new self();
    }

    public function shiftHeadingLevelBy(int $level): self
    {
        return $this->set('--shift-heading-level-by', (string) $level);
    }

    public function fileScope(bool $fileScope = true): self
    {
        return $this->bool('--file-scope', $fileScope);
    }

    public function preserveTabs(bool $preserveTabs = true): self
    {
        return $this->bool('--preserve-tabs', $preserveTabs);
    }

    public function tabStop(int $tabStop): self
    {
        return $this->set('--tab-stop', (string) $tabStop);
    }

    public function dataDir(string $dir): self
    {
        return $this->set('--data-dir', $dir);
    }

    public function failIfWarnings(bool $fail = true): self
    {
        return $this->bool('--fail-if-warnings', $fail);
    }

    public function idPrefix(string $prefix): self
    {
        return $this->string('--id-prefix', $prefix);
    }

    public function listTables(bool $list = true): self
    {
        return $this->bool('--list-tables', $list);
    }

    public function output(string $output): self
    {
        return $this->string('-o', $output);
    }

    public function outputFormat(string $format): self
    {
        return $this->string('-t', $format);
    }

    public function referenceLinks(bool $referenceLinks = true): self
    {
        return $this->bool('--reference-links', $referenceLinks);
    }

    public function sandbox(bool $sandbox = true): self
    {
        return $this->bool('--sandbox', $sandbox);
    }

    public function standalone(bool $standalone = true): self
    {
        return $this->bool('--standalone', $standalone);
    }

    public function wrap(string $wrap = 'auto'): self
    {
        if (!in_array($wrap, ['auto', 'none', 'preserve'], true)) {
            throw new \InvalidArgumentException(sprintf('Invalid wrap value: "%s". Expected "auto", "none" or "preserve".', $wrap));
        }

        return $this->string('--wrap', $wrap);
    }

    public function columns(int $columns): self
    {
        return $this->set('--columns', (string) $columns);
    }

    public function toc(bool $toc = true): self
    {
        return $this->bool('--toc', $toc);
    }

    public function tocDepth(int $depth): self
    {
        return $this->set('--toc-depth', (string) $depth);
    }

    public function numberSections(bool $number = true): self
    {
        return $this->bool('--number-sections', $number);
    }

    public function stripComments(bool $strip = true): self
    {
        return $this->bool('--strip-comments', $strip);
    }

    public function tableOfContent(bool $toc = true): self
    {
        return $this->bool('--toc', $toc);
    }

    public function titlePrefix(string $string): self
    {
        return $this->string('--title-prefix', $string);
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

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $options = $this->options;
        ksort($options, SORT_NATURAL);

        return $options;
    }

    public function __toString(): string
    {
        return implode(' ', array_map(function (string $name, string $value): string {
            return $name . '=' . $value;
        }, array_keys($this->options), $this->options));
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
}
