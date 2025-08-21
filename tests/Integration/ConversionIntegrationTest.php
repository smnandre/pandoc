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
final class ConversionIntegrationTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        if (!Pandoc::isInstalled()) {
            $this->markTestSkipped('Pandoc not installed');
        }

        $this->tempDir = sys_get_temp_dir().'/pandoc_conversion_test_'.uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->cleanupPath($this->tempDir);
        }
    }

    public function testMarkdownToHtmlFileConversion(): void
    {
        $markdown = "# Hello World\n\nThis is a **minimal** test document.";
        $inputFile = $this->createTempFile($markdown, 'md');
        $outputFile = $this->tempDir.'/output.html';

        try {
            $result = Pandoc::convertFile($inputFile, $outputFile);

            $this->assertTrue($result->isSuccess());
            $this->assertFileExists($outputFile);

            $actualOutput = file_get_contents($outputFile);
            $this->assertStringContainsString('<h1', $actualOutput);
            $this->assertStringContainsString('Hello World', $actualOutput);
            $this->assertStringContainsString('<strong>minimal</strong>', $actualOutput);
        } finally {
            $this->cleanupPath($inputFile);
            $this->cleanupPath($outputFile);
        }
    }

    public function testStringToStringConversion(): void
    {
        $markdownContent = "# Test Title\n\nThis is **bold** text.";

        $htmlOutput = Pandoc::markdownToHtml($markdownContent);

        $this->assertStringContainsString('<h1', $htmlOutput);
        $this->assertStringContainsString('Test Title', $htmlOutput);
        $this->assertStringContainsString('<strong>bold</strong>', $htmlOutput);
    }

    public function testConversionMeasuresDuration(): void
    {
        $markdown = "# Test Document\n\nSome content.";
        $inputFile = $this->createTempFile($markdown, 'md');
        $outputFile = $this->tempDir.'/timed.html';

        try {
            $result = Pandoc::convertFile($inputFile, $outputFile);

            $this->assertTrue($result->isSuccess());
            $this->assertIsFloat($result->getDuration());
            $this->assertGreaterThan(0, $result->getDuration());
        } finally {
            $this->cleanupPath($inputFile);
            $this->cleanupPath($outputFile);
        }
    }

    public function testConverterBuilderPattern(): void
    {
        $markdown = "# Builder Test\n\nTesting the **builder** pattern.";

        $converter = Pandoc::converter()
            ->content($markdown)
            ->to('html')
            ->option('standalone', false);

        $result = $converter->getContent();

        $this->assertStringContainsString('<h1', $result);
        $this->assertStringContainsString('Builder Test', $result);
        $this->assertStringContainsString('<strong>builder</strong>', $result);
    }

    private function createTempFile(string $content, string $extension): string
    {
        $filename = $this->tempDir.'/'.uniqid().'.'.$extension;
        file_put_contents($filename, $content);

        return $filename;
    }

    private function cleanupPath(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
        } elseif (is_dir($path)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {
                if ($fileinfo->isDir()) {
                    rmdir($fileinfo->getRealPath());
                } else {
                    unlink($fileinfo->getRealPath());
                }
            }
            rmdir($path);
        }
    }
}
