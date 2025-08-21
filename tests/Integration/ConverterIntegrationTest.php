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

namespace Pandoc\Tests\Integration;

use Pandoc\Conversion;
use Pandoc\Converter;
use Pandoc\Format;
use Pandoc\Options;
use Pandoc\Pandoc;
use Pandoc\PandocBinary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Converter::class)]
#[UsesClass(Conversion::class)]
#[UsesClass(Format::class)]
#[UsesClass(Options::class)]
#[UsesClass(Pandoc::class)]
#[UsesClass(PandocBinary::class)]
final class ConverterIntegrationTest extends TestCase
{
    private string $fixturesDir;

    protected function setUp(): void
    {
        if (!Pandoc::isInstalled()) {
            $this->markTestSkipped('Pandoc not installed');
        }

        $this->fixturesDir = __DIR__.'/../Fixtures';
    }

    public function testMinimalMarkdownToHtml(): void
    {
        $inputFile = $this->fixturesDir.'/input/minimal.md';
        $expectedFile = $this->fixturesDir.'/output/minimal.html';

        $converter = Converter::create()
            ->file($inputFile)
            ->to('html')
            ->option('standalone', false);

        $actualOutput = $converter->getContent();
        $expectedOutput = file_get_contents($expectedFile);

        $this->assertEquals(trim($expectedOutput), trim($actualOutput));
    }

    public function testSimpleMarkdownToHtml(): void
    {
        $inputFile = $this->fixturesDir.'/input/simple.md';
        $expectedFile = $this->fixturesDir.'/output/simple.html';

        $converter = Converter::create()
            ->file($inputFile)
            ->to('html')
            ->option('standalone', false);

        $actualOutput = $converter->getContent();
        $expectedOutput = file_get_contents($expectedFile);

        $this->assertEquals(trim($expectedOutput), trim($actualOutput));
    }

    public function testHtmlToMarkdown(): void
    {
        $inputFile = $this->fixturesDir.'/input/simple.html';
        $expectedFile = $this->fixturesDir.'/output/simple.md';

        $converter = Converter::create()
            ->file($inputFile)
            ->from('html')
            ->to('markdown');

        $actualOutput = $converter->getContent();
        $expectedOutput = file_get_contents($expectedFile);

        $this->assertEquals(trim($expectedOutput), trim($actualOutput));
    }

    public function testBatch1MarkdownToHtml(): void
    {
        $inputFile = $this->fixturesDir.'/input/batch1.md';
        $expectedFile = $this->fixturesDir.'/output/batch1.html';

        $converter = Converter::create()
            ->file($inputFile)
            ->to('html')
            ->option('standalone', false);

        $actualOutput = $converter->getContent();
        $expectedOutput = file_get_contents($expectedFile);

        $this->assertEquals(trim($expectedOutput), trim($actualOutput));
    }

    public function testBatch2MarkdownToHtml(): void
    {
        $inputFile = $this->fixturesDir.'/input/batch2.md';
        $expectedFile = $this->fixturesDir.'/output/batch2.html';

        $converter = Converter::create()
            ->file($inputFile)
            ->to('html')
            ->option('standalone', false);

        $actualOutput = $converter->getContent();
        $expectedOutput = file_get_contents($expectedFile);

        $this->assertEquals(trim($expectedOutput), trim($actualOutput));
    }
}
