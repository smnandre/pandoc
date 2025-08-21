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

#[CoversClass(Pandoc::class)]
#[CoversClass(Converter::class)]
#[UsesClass(Conversion::class)]
#[UsesClass(PandocBinary::class)]
#[UsesClass(Format::class)]
#[UsesClass(Options::class)]
final class PandocIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        if (!Pandoc::isInstalled()) {
            $this->markTestSkipped('Pandoc not installed');
        }
    }

    public function testConvertStringToString(): void
    {
        $markdown = '# Hello World';
        $html = Pandoc::convert($markdown, 'html');

        $this->assertStringContainsString('<h1', $html);
        $this->assertStringContainsString('Hello World', $html);
    }

    public function testMarkdownToHtml(): void
    {
        $markdown = '# Title\n\nSome **bold** text.';
        $html = Pandoc::markdownToHtml($markdown);

        $this->assertStringContainsString('<h1', $html);
        $this->assertStringContainsString('<strong>bold</strong>', $html);
    }

    public function testHtmlToMarkdown(): void
    {
        $html = '<h1>Title</h1><p>Some <strong>bold</strong> text.</p>';
        $markdown = Pandoc::htmlToMarkdown($html);

        $this->assertStringContainsString('# Title', $markdown);
        $this->assertStringContainsString('**bold**', $markdown);
    }

    public function testConvertFile(): void
    {
        $inputFile = '/tmp/test.md';
        $outputFile = '/tmp/test.html';

        file_put_contents($inputFile, '# Test Document');

        try {
            $conversion = Pandoc::convertFile($inputFile, $outputFile);

            $this->assertTrue($conversion->isSuccess());
            $this->assertTrue(file_exists($outputFile));
        } finally {
            if (file_exists($inputFile)) {
                unlink($inputFile);
            }
            if (file_exists($outputFile)) {
                unlink($outputFile);
            }
        }
    }

    public function testConverterBuilderPattern(): void
    {
        $result = Pandoc::content('# Test')
                       ->to('html')
                       ->with(['standalone' => false])
                       ->getContent();

        $this->assertStringContainsString('<h1', $result);
        $this->assertStringContainsString('Test', $result);
    }

    public function testVariablesAndMetadata(): void
    {
        $result = Pandoc::content('# Test Document')
                       ->to('html')
                       ->variable('title', 'My Document')
                       ->metadata('author', 'John Doe')
                       ->getContent();

        $this->assertStringContainsString('Test Document', $result);
    }

    public function testConvertFileWithDirectoryAsOutput(): void
    {
        $inputFile = sys_get_temp_dir().'/pandoc_it_'.uniqid('', true).'.md';
        $outputDir = sys_get_temp_dir().'/pandoc_out_'.uniqid('', true);

        file_put_contents($inputFile, '# Test Document');
        mkdir($outputDir, 0777, true);

        try {
            $result = Pandoc::file($inputFile)->to('html')->output($outputDir)->convert();

            $this->assertInstanceOf(Conversion::class, $result);
            $this->assertTrue($result->isSuccess());

            $expectedOutput = $outputDir.'/'.pathinfo($inputFile, \PATHINFO_FILENAME).'.html';
            $this->assertSame($expectedOutput, $result->getPath());
        } finally {
            if (file_exists($inputFile)) {
                unlink($inputFile);
            }
            if (is_dir($outputDir)) {
                $files = glob($outputDir.'/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                rmdir($outputDir);
            }
        }
    }
}
