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

namespace Pandoc\Tests\Unit;

use Pandoc\Conversion;
use Pandoc\Converter;
use Pandoc\ConverterInterface;
use Pandoc\Format;
use Pandoc\Options;
use Pandoc\Pandoc;
use Pandoc\Test\MockConverter;
use Pandoc\Test\MockPandocBinary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Pandoc::class)]
#[UsesClass(Conversion::class)]
#[UsesClass(Converter::class)]
#[UsesClass(Format::class)]
#[UsesClass(Options::class)]
#[UsesClass(MockPandocBinary::class)]
#[UsesClass(MockConverter::class)]
final class PandocTest extends TestCase
{
    private MockPandocBinary $mockBinary;
    private MockConverter $mockConverter;

    protected function setUp(): void
    {
        $this->mockBinary = new MockPandocBinary();
        $this->mockConverter = new MockConverter();
        Pandoc::setDefaultBinary($this->mockBinary);
        Pandoc::setConverter($this->mockConverter);
    }

    protected function tearDown(): void
    {
        Pandoc::resetDefaultBinary();
        Pandoc::resetConverter();
    }

    public function testConvertStringToString(): void
    {
        $markdown = '# Hello World';
        $html = Pandoc::convert($markdown, 'html');

        $this->assertStringContainsString('<h1', $html);
    }

    public function testMarkdownToHtml(): void
    {
        $markdown = '# Title\n\nSome **bold** text.';
        $html = Pandoc::markdownToHtml($markdown);

        $this->assertStringContainsString('<h1', $html);
    }

    public function testHtmlToMarkdown(): void
    {
        $html = '<h1>Title</h1><p>Some <strong>bold</strong> text.</p>';
        $markdown = Pandoc::htmlToMarkdown($html);

        $this->assertIsString($markdown);
    }

    public function testConvertFile(): void
    {
        $inputFile = '/tmp/test.md';
        $outputFile = '/tmp/test.html';

        file_put_contents($inputFile, '# Test Document');

        try {
            $conversion = Pandoc::convertFile($inputFile, $outputFile);

            $this->assertInstanceOf(Conversion::class, $conversion);
            $this->assertTrue($conversion->isSuccess());
        } finally {
            if (file_exists($inputFile)) {
                unlink($inputFile);
            }
            if (file_exists($outputFile)) {
                unlink($outputFile);
            }
        }
    }

    public function testConvertFileToString(): void
    {
        $inputFile = '/tmp/test.md';
        file_put_contents($inputFile, '# Test Document');

        try {
            $html = Pandoc::file($inputFile)->from('gfm')->to('html')->getContent();
            $this->assertStringContainsString('<h1', $html);
        } finally {
            if (file_exists($inputFile)) {
                unlink($inputFile);
            }
        }
    }

    public function testConvertStringToFile(): void
    {
        $markdown = '# Test Document';
        $outputFile = '/tmp/test.html';

        try {
            $conversion = Pandoc::content($markdown)->output($outputFile)->to('html')->convert();

            $this->assertInstanceOf(Conversion::class, $conversion);
            $this->assertTrue($conversion->isSuccess());
            $this->assertSame($outputFile, $conversion->getPath());
        } finally {
            if (file_exists($outputFile)) {
                unlink($outputFile);
            }
        }
    }

    public function testConvertFiles(): void
    {
        $inputFiles = ['/tmp/test1.md', '/tmp/test2.md'];
        $outputDir = '/tmp/output';

        mkdir($outputDir, 0777, true);

        try {
            foreach ($inputFiles as $file) {
                file_put_contents($file, '# Test Document');
            }

            $conversions = Pandoc::convertFiles($inputFiles, $outputDir, 'html');

            $this->assertCount(2, $conversions);
            $this->assertContainsOnlyInstancesOf(Conversion::class, $conversions);

            foreach ($conversions as $conversion) {
                $this->assertTrue($conversion->isSuccess());
            }
        } finally {
            foreach ($inputFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
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

    public function testIsInstalled(): void
    {
        $this->assertTrue(Pandoc::isInstalled());
    }

    public function testVersion(): void
    {
        $version = Pandoc::version();
        $this->assertIsString($version);
        $this->assertNotEmpty($version);
        $this->assertSame('3.5.0', $version);
    }

    public function testInputFormats(): void
    {
        $formats = Pandoc::inputFormats();

        $this->assertIsArray($formats);
        $this->assertContains('markdown', $formats);
        $this->assertContains('html', $formats);
    }

    public function testOutputFormats(): void
    {
        $formats = Pandoc::outputFormats();

        $this->assertIsArray($formats);
        $this->assertContains('html', $formats);
        $this->assertContains('pdf', $formats);
    }

    public function testSupports(): void
    {
        $this->assertTrue(Pandoc::supports('markdown', 'html'));
        $this->assertFalse(Pandoc::supports('invalid', 'format'));
    }

    public function testCanConvertFileExistingFile(): void
    {
        $inputFile = '/tmp/test.md';
        $outputFile = '/tmp/test.html';

        file_put_contents($inputFile, '# Test');

        try {
            $this->assertTrue(Pandoc::canConvertFile($inputFile, $outputFile));
        } finally {
            if (file_exists($inputFile)) {
                unlink($inputFile);
            }
        }
    }

    public function testCanConvertFileNonExistingFile(): void
    {
        $this->assertFalse(Pandoc::canConvertFile('/tmp/nonexistent.md', '/tmp/output.html'));
    }

    public function testGetSuggestedFormats(): void
    {
        $inputFile = '/tmp/test.md';
        file_put_contents($inputFile, '# Test');

        try {
            $formats = Pandoc::getSuggestedFormats($inputFile);

            $this->assertIsArray($formats);
            $this->assertNotEmpty($formats);
            $this->assertContainsOnlyInstancesOf(Format::class, $formats);
        } finally {
            if (file_exists($inputFile)) {
                unlink($inputFile);
            }
        }
    }

    public function testConverter(): void
    {
        $converter = Pandoc::converter();

        $this->assertInstanceOf(ConverterInterface::class, $converter);
    }

    public function testConverterWithCustomBinary(): void
    {
        $customBinary = new MockPandocBinary('/custom/pandoc');
        $converter = Pandoc::converter($customBinary);

        $this->assertInstanceOf(ConverterInterface::class, $converter);
    }

    public function testBuilderMethods(): void
    {
        $converter = Pandoc::content('# Test')->to('html');
        $this->assertInstanceOf(ConverterInterface::class, $converter);

        $converter = Pandoc::to('html');
        $this->assertInstanceOf(ConverterInterface::class, $converter);

        $converter = Pandoc::with(['toc' => true]);
        $this->assertInstanceOf(ConverterInterface::class, $converter);
    }

    public function testQuickConversionMethods(): void
    {
        $result = Pandoc::convert('# Test', 'html');
        $this->assertStringContainsString('<h1', $result);

        $result = Pandoc::convert('# Test', 'html', ['toc' => true]);
        $this->assertStringContainsString('<h1', $result);
    }

    public function testOptionsCreation(): void
    {
        $options = Pandoc::options(['toc' => true, 'standalone' => true]);
        $this->assertInstanceOf(Options::class, $options);

        $optionsArray = $options->toArray();
        $this->assertTrue($optionsArray['options']['toc']);
        $this->assertTrue($optionsArray['options']['standalone']);
    }

    public function testSetAndResetConverter(): void
    {
        $mockConverter = new MockConverter();
        Pandoc::setConverter($mockConverter);
        Pandoc::resetConverter();
        $newConverter = Pandoc::converter();

        $this->assertNotSame($mockConverter, $newConverter);
    }

    public function testSetAndResetDefaultBinary(): void
    {
        $mockBinary = new MockPandocBinary();

        Pandoc::setDefaultBinary($mockBinary);

        Pandoc::resetDefaultBinary();

        $this->assertTrue(true);
    }

    public function testGetConverterWithSetConverter(): void
    {
        $mockConverter = $this->createMock(ConverterInterface::class);
        Pandoc::setConverter($mockConverter);
        $result = Pandoc::content('# Test');
        Pandoc::resetConverter();

        $this->assertInstanceOf(ConverterInterface::class, $result);
    }

    public function testConvertWithOptions(): void
    {
        $html = Pandoc::convert('# Test Document', 'html', ['standalone' => true]);

        $this->assertStringContainsString('<h1', $html);
    }

    public function testConvertFileWithOptions(): void
    {
        $inputFile = '/tmp/test.md';
        $outputFile = '/tmp/test.html';

        file_put_contents($inputFile, '# Test Document');

        try {
            $conversion = Pandoc::convertFile($inputFile, $outputFile, ['toc' => true]);

            $this->assertInstanceOf(Conversion::class, $conversion);
            $this->assertTrue($conversion->isSuccess());
        } finally {
            if (file_exists($inputFile)) {
                unlink($inputFile);
            }
            if (file_exists($outputFile)) {
                unlink($outputFile);
            }
        }
    }

    public function testGetBinaryMethod(): void
    {
        $isInstalled = Pandoc::isInstalled();
        $this->assertIsBool($isInstalled);
    }

    public function testGetSuggestedFormatsWithNonExistentFile(): void
    {
        $formats = Pandoc::getSuggestedFormats('/nonexistent/file.md');
        $this->assertIsArray($formats);
        $this->assertNotEmpty($formats);
        $this->assertContainsOnlyInstancesOf(Format::class, $formats);
    }

    public function testCanConvertFileWithInvalidExtensions(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, '# Test');
        $unknownFile = $tempFile.'.unknown';
        rename($tempFile, $unknownFile);

        try {
            $result = Pandoc::canConvertFile($unknownFile, 'output.unknown2');
            $this->assertFalse($result);
        } finally {
            if (file_exists($unknownFile)) {
                unlink($unknownFile);
            }
        }
    }

    public function testSupportsWithUnsupportedFormats(): void
    {
        $this->assertFalse(Pandoc::supports('nonexistent', 'format'));
        $this->assertFalse(Pandoc::supports('markdown', 'nonexistent'));
    }

    public function testGetSuggestedFormatsWithUnsupportedInput(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'test content');

        $unsupportedFile = $tempFile.'.xyz';
        rename($tempFile, $unsupportedFile);

        try {
            $formats = Pandoc::getSuggestedFormats($unsupportedFile);
            $this->assertIsArray($formats);
            $this->assertEmpty($formats);
        } finally {
            if (file_exists($unsupportedFile)) {
                unlink($unsupportedFile);
            }
        }
    }

    public function testConvertFilesWithEmptyArray(): void
    {
        $outputDir = '/tmp/output';
        mkdir($outputDir, 0777, true);

        try {
            $results = Pandoc::convertFiles([], $outputDir, 'html');
            $this->assertIsArray($results);
            $this->assertEmpty($results);
        } finally {
            if (is_dir($outputDir)) {
                rmdir($outputDir);
            }
        }
    }

    public function testVersionMethodWhenPandocNotInstalled(): void
    {
        $this->mockBinary->shouldFail(true, 'Binary unavailable');

        $this->expectException(\Exception::class);
        Pandoc::version();
    }

    public function testInputFormatsWhenPandocNotInstalled(): void
    {
        $this->mockBinary->shouldFail(true, 'Binary unavailable');

        $this->expectException(\Exception::class);
        Pandoc::inputFormats();
    }

    public function testOutputFormatsWhenPandocNotInstalled(): void
    {
        $this->mockBinary->shouldFail(true, 'Binary unavailable');

        $this->expectException(\Exception::class);
        Pandoc::outputFormats();
    }

    public function testSupportsWhenPandocNotInstalled(): void
    {
        $this->mockBinary->shouldFail(true, 'Binary unavailable');

        $this->expectException(\Exception::class);
        Pandoc::supports('markdown', 'html');
    }

    public function testBuilderPatternsWithComplexChaining(): void
    {
        $result = Pandoc::content('# Test')
                        ->to('html')
                        ->with(['toc' => true, 'standalone' => false])
                        ->getContent();

        $this->assertIsString($result);
    }

    public function testWithMethodWithEmptyOptions(): void
    {
        $converter = Pandoc::with([]);
        $this->assertInstanceOf(ConverterInterface::class, $converter);
    }
}
