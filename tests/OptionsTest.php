<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests;

use Pandoc\Options;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Options::class)]
class OptionsTest extends TestCase
{
    public function testDefaultOptions(): void
    {
        $options = Options::create();

        $this->assertCount(0, $options);
    }

    public function testCount(): void
    {
        $options = Options::create();
        $this->assertCount(0, $options);

        $options = $options->dataDir('dir');
        $this->assertCount(1, $options);

        $options = $options->dataDir('dir');
        $this->assertCount(1, $options);

        $options = $options->idPrefix('string');
        $this->assertCount(2, $options);
    }

    public function testGeneralOptions(): void
    {
        $options = Options::create()
            // ->from('markdown+emoji')
            // ->to('markdown+hard_line_breaks')
            // ->output('foo.pdf')
            // ->output('-')
            ->dataDir('dir')
            // ->defaults('file')
            //    ->verbose()
            //    ->quiet()
            ->failIfWarnings()
            ->sandbox();

        $this->assertOption('--data-dir', 'dir', $options);
        $this->assertOption('--fail-if-warnings', 'true', $options);
        $this->assertOption('--sandbox', 'true', $options);
    }

    public function testReaderOptions(): void
    {
        // https://pandoc.org/MANUAL.html#reader-options-1

        $options = Options::create()
            ->shiftHeadingLevelBy(1)
            ->fileScope()
            ->preserveTabs()
            ->tabStop(4)
        ;

        $this->assertOption('--shift-heading-level-by', '1', $options);
        $this->assertOption('--file-scope', 'true', $options);
        $this->assertOption('--preserve-tabs', 'true', $options);
        $this->assertOption('--tab-stop', '4', $options);
    }

    public function testWriterOptions(): void
    {
        $options = Options::create()
            ->standalone()
            ->wrap('preserve')
            ->columns(80)
            ->stripComments()
            ->toc()
            ->tocDepth(2);

        $this->assertOption('--standalone', 'true', $options);
        $this->assertOption('--wrap', 'preserve', $options);
        $this->assertOption('--columns', '80', $options);
        $this->assertOption('--strip-comments', 'true', $options);
        $this->assertOption('--toc', 'true', $options);
        $this->assertOption('--toc-depth', '2', $options);
    }

    private function assertOption(string $name, string $value, Options $options): void
    {
        $options = iterator_to_array($options);

        $this->assertArrayHasKey($name, $options);
        $this->assertSame($value, $options[$name]);
    }
}
