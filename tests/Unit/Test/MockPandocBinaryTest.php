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

namespace Pandoc\Tests\Unit\Test;

use Pandoc\Conversion;
use Pandoc\Options;
use Pandoc\Test\MockPandocBinary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MockPandocBinary::class)]
#[UsesClass(Conversion::class)]
#[UsesClass(Options::class)]
final class MockPandocBinaryTest extends TestCase
{
    private MockPandocBinary $mockBinary;

    protected function setUp(): void
    {
        $this->mockBinary = new MockPandocBinary();
    }

    public function testConstructorWithDefaultPath(): void
    {
        $binary = new MockPandocBinary();
        $this->assertSame('/mock/pandoc', $binary->getBinaryPath());
    }

    public function testConstructorWithCustomPath(): void
    {
        $customPath = '/custom/path/pandoc';
        $binary = new MockPandocBinary($customPath);
        $this->assertSame($customPath, $binary->getBinaryPath());
    }

    public function testGetVersion(): void
    {
        $version = $this->mockBinary->getVersion();
        $this->assertSame('3.5.0', $version);
    }

    public function testSetVersion(): void
    {
        $newVersion = '2.19.2';
        $result = $this->mockBinary->setVersion($newVersion);

        $this->assertSame($this->mockBinary, $result);
        $this->assertSame($newVersion, $this->mockBinary->getVersion());
    }

    public function testGetInputFormats(): void
    {
        $formats = $this->mockBinary->getInputFormats();

        $this->assertIsArray($formats);
        $this->assertContains('markdown', $formats);
        $this->assertContains('html', $formats);
        $this->assertContains('docx', $formats);
        $this->assertCount(16, $formats);
    }

    public function testSetInputFormats(): void
    {
        $customFormats = ['markdown', 'rst', 'asciidoc'];
        $result = $this->mockBinary->setInputFormats($customFormats);

        $this->assertSame($this->mockBinary, $result);
        $this->assertSame($customFormats, $this->mockBinary->getInputFormats());
    }

    public function testGetOutputFormats(): void
    {
        $formats = $this->mockBinary->getOutputFormats();

        $this->assertIsArray($formats);
        $this->assertContains('html', $formats);
        $this->assertContains('pdf', $formats);
        $this->assertContains('docx', $formats);
        $this->assertCount(18, $formats);
    }

    public function testSetOutputFormats(): void
    {
        $customFormats = ['html', 'pdf', 'epub'];
        $result = $this->mockBinary->setOutputFormats($customFormats);

        $this->assertSame($this->mockBinary, $result);
        $this->assertSame($customFormats, $this->mockBinary->getOutputFormats());
    }

    public function testGetHighlightLanguages(): void
    {
        $languages = $this->mockBinary->getHighlightLanguages();

        $this->assertIsArray($languages);
        $this->assertContains('php', $languages);
        $this->assertContains('python', $languages);
        $this->assertCount(5, $languages);
    }

    public function testGetHighlightStyles(): void
    {
        $styles = $this->mockBinary->getHighlightStyles();

        $this->assertIsArray($styles);
        $this->assertContains('tango', $styles);
        $this->assertContains('pygments', $styles);
        $this->assertCount(4, $styles);
    }

    public function testSupports(): void
    {
        $this->assertTrue($this->mockBinary->supports('markdown', 'html'));
        $this->assertTrue($this->mockBinary->supports('html', 'pdf'));
        $this->assertFalse($this->mockBinary->supports('nonexistent', 'format'));
        $this->assertFalse($this->mockBinary->supports('markdown', 'nonexistent'));
    }

    public function testIsAvailable(): void
    {
        $this->assertTrue($this->mockBinary->isAvailable());
    }

    public function testIsInstalled(): void
    {
        $this->assertTrue(MockPandocBinary::isInstalled());
    }

    public function testGetCapabilities(): void
    {
        $capabilities = $this->mockBinary->getCapabilities();

        $this->assertIsArray($capabilities);
        $this->assertSame('3.5.0', $capabilities['version']);
        $this->assertSame('/mock/pandoc', $capabilities['binary_path']);
        $this->assertIsArray($capabilities['input_formats']);
        $this->assertIsArray($capabilities['output_formats']);
        $this->assertIsArray($capabilities['highlight_languages']);
        $this->assertIsArray($capabilities['highlight_styles']);
        $this->assertIsInt($capabilities['loaded_at']);
    }

    public function testExecuteWithStringContent(): void
    {
        $conversion = new Conversion(
            inputFormat: 'markdown',
            outputFormat: 'html'
        );
        $conversion->inputContent = '# Test Document';

        $result = $this->mockBinary->execute($conversion);

        $this->assertIsString($result);
        $this->assertStringContainsString('<h1', $result);
        $this->assertStringContainsString('Test Document', $result);
    }

    public function testExecuteWithFileInput(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, '# File Test Document');

        try {
            $conversion = new Conversion(
                inputFormat: 'markdown',
                outputFormat: 'html'
            );
            $conversion->inputPath = $tempFile;

            $result = $this->mockBinary->execute($conversion);

            $this->assertIsString($result);
            $this->assertStringContainsString('<h1', $result);
            $this->assertStringContainsString('File Test Document', $result);
        } finally {
            unlink($tempFile);
        }
    }

    public function testExecuteWithMissingFileError(): void
    {
        $nonExistentPath = '/this/path/does/not/exist/file.md';

        $conversion = new Conversion(
            inputFormat: 'markdown',
            outputFormat: 'html'
        );
        $conversion->inputContent = $nonExistentPath;

        $result = $this->mockBinary->execute($conversion);

        $this->assertStringContainsString('/this/path/does/not/exist/file.md', $result);
    }

    public function testExecuteWithDifferentOutputFormats(): void
    {
        $testCases = [
            'pdf' => '%PDF-1.4',
            'latex' => '\\documentclass{article}',
            'json' => '{"mock"',
            'plain' => 'Test Document',
            'unknown' => 'Mock unknown output from:',
        ];

        foreach ($testCases as $format => $expectedContent) {
            $conversion = new Conversion(
                inputFormat: 'markdown',
                outputFormat: $format
            );
            $conversion->inputContent = '# Test Document';

            $result = $this->mockBinary->execute($conversion);
            $this->assertStringContainsString($expectedContent, $result);
        }
    }

    public function testConvertSuccessful(): void
    {
        $conversion = new Conversion(
            inputFormat: 'markdown',
            outputFormat: 'html'
        );
        $conversion->inputContent = '# Test Document';

        $result = $this->mockBinary->convert($conversion);

        $this->assertInstanceOf(Conversion::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertIsFloat($result->getDuration());
        $this->assertGreaterThan(0, $result->getDuration());
        $this->assertStringContainsString('<h1', $result->getContent());
    }

    public function testConvertWithFailureFlag(): void
    {
        $this->mockBinary->shouldFail(true, 'Custom error message');

        $conversion = new Conversion(
            inputFormat: 'markdown',
            outputFormat: 'html'
        );
        $conversion->inputContent = '# Test Document';

        $result = $this->mockBinary->convert($conversion);

        $this->assertInstanceOf(Conversion::class, $result);
        $this->assertFalse($result->isSuccess());
        $this->assertSame('Custom error message', $result->getError());
        $this->assertIsFloat($result->getDuration());
    }

    public function testShouldFailWithDefaultError(): void
    {
        $this->mockBinary->shouldFail(true);

        $conversion = new Conversion(
            inputFormat: 'markdown',
            outputFormat: 'html'
        );
        $conversion->inputContent = '# Test Document';

        $result = $this->mockBinary->convert($conversion);

        $this->assertFalse($result->isSuccess());
        $this->assertSame('Mock conversion failed', $result->getError());
    }

    public function testExecuteWithFailureFlag(): void
    {
        $this->mockBinary->shouldFail(true, 'Execution error');

        $conversion = new Conversion(
            inputFormat: 'markdown',
            outputFormat: 'html'
        );
        $conversion->inputContent = '# Test Document';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Execution error');

        $this->mockBinary->execute($conversion);
    }

    public function testShouldFailReturnsItself(): void
    {
        $result = $this->mockBinary->shouldFail(true, 'Error message');
        $this->assertSame($this->mockBinary, $result);
    }

    public function testIsAvailableWhenShouldFail(): void
    {
        $this->mockBinary->shouldFail(true);
        $this->assertFalse($this->mockBinary->isAvailable());
    }

    public function testChainedConfigurationMethods(): void
    {
        $result = $this->mockBinary
            ->setVersion('2.19.2')
            ->setInputFormats(['markdown', 'rst'])
            ->setOutputFormats(['html', 'pdf'])
            ->shouldFail(false);

        $this->assertSame($this->mockBinary, $result);
        $this->assertSame('2.19.2', $this->mockBinary->getVersion());
        $this->assertSame(['markdown', 'rst'], $this->mockBinary->getInputFormats());
        $this->assertSame(['html', 'pdf'], $this->mockBinary->getOutputFormats());
        $this->assertTrue($this->mockBinary->isAvailable());
    }

    public function testGetVersionWhenShouldFail(): void
    {
        $this->mockBinary->shouldFail(true);

        $this->expectException(\RuntimeException::class);
        $this->mockBinary->getVersion();
    }

    public function testGetOutputFoematWhenShouldFail(): void
    {
        $this->mockBinary->shouldFail(true);

        $this->expectException(\RuntimeException::class);
        $this->mockBinary->getOutputFormats();
    }

    public function testGetFormatsWhenShouldFail(): void
    {
        $this->mockBinary->shouldFail(true);

        $this->expectException(\RuntimeException::class);
        $this->mockBinary->getInputFormats();
    }

    public function testExecuteWithActualFileInput(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, '# Actual File');

        try {
            $conversion = new Conversion(
                inputFormat: 'markdown',
                outputFormat: 'html'
            );
            $conversion->inputPath = $tempFile;
            $conversion->inputContent = null;

            $result = $this->mockBinary->execute($conversion);

            $this->assertStringContainsString('Actual File', $result);
        } finally {
            unlink($tempFile);
        }
    }

    public function testExecuteWithNonExistentFilePath(): void
    {
        $conversion = new Conversion(
            inputFormat: 'markdown',
            outputFormat: 'html'
        );
        $conversion->inputPath = '/nonexistent/file.md';
        $conversion->inputContent = null;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to read input file: /nonexistent/file.md');

        $this->mockBinary->execute($conversion);
    }

    public function testConvertWithExecuteException(): void
    {
        $conversion = new Conversion(
            inputFormat: 'markdown',
            outputFormat: 'html'
        );
        $conversion->inputPath = '/nonexistent/file.md';
        $conversion->inputContent = null;

        $result = $this->mockBinary->convert($conversion);

        $this->assertFalse($result->isSuccess());
        $this->assertStringContainsString('Failed to read input file', $result->getError());
        $this->assertGreaterThan(0, $result->getDuration());
    }

    public function testSupportsWhenShouldFail(): void
    {
        $this->mockBinary->shouldFail(true, 'Binary not available');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Binary not available');

        $this->mockBinary->supports('markdown', 'html');
    }

    public function testMockToHtmlWithComplexContent(): void
    {
        $conversion = new Conversion(
            inputFormat: 'markdown',
            outputFormat: 'html'
        );
        $conversion->inputContent = "# Main Title!\n## Sub Title?\nSome **bold text** here.\nAnother line.";

        $result = $this->mockBinary->execute($conversion);

        $this->assertStringContainsString('<h1 id="main-title">Main Title!</h1>', $result);
        $this->assertStringContainsString('<h2 id="sub-title">Sub Title?</h2>', $result);
        $this->assertStringContainsString('<strong>bold text</strong>', $result);
        $this->assertStringContainsString('<p>Some <strong>bold text</strong> here.</p>', $result);
    }

    public function testMockToMarkdownWithHtmlInput(): void
    {
        $conversion = new Conversion(
            inputFormat: 'html',
            outputFormat: 'markdown'
        );
        $conversion->inputContent = '<h1>Header 1</h1><h2>Header 2</h2><p>Text with <strong>bold</strong> content.</p>';

        $result = $this->mockBinary->execute($conversion);

        $this->assertStringContainsString('# Header 1', $result);
        $this->assertStringContainsString('## Header 2', $result);
        $this->assertStringContainsString('**bold**', $result);
    }

    public function testMockToLatexWithMarkdownInput(): void
    {
        $conversion = new Conversion(
            inputFormat: 'markdown',
            outputFormat: 'latex'
        );
        $conversion->inputContent = "# Section Title\n## Subsection Title\nText with **bold** formatting.";

        $result = $this->mockBinary->execute($conversion);

        $this->assertStringContainsString('\\documentclass{article}', $result);
        $this->assertStringContainsString('\\section{Section Title}', $result);
        $this->assertStringContainsString('\\subsection{Subsection Title}', $result);
        $this->assertStringContainsString('\\textbf{bold}', $result);
    }

    public function testStaticInstalledFlag(): void
    {
        $binary1 = new MockPandocBinary();
        $this->assertTrue(MockPandocBinary::isInstalled());

        $binary1->shouldFail(true);
        $this->assertFalse(MockPandocBinary::isInstalled());

        $binary2 = new MockPandocBinary();
        $binary2->shouldFail(false);
        $this->assertTrue(MockPandocBinary::isInstalled());
    }

    public function testExecuteConvertsNonEmptyLinesToHtmlParagraphs(): void
    {
        $conversion = new Conversion('markdown', 'html');
        $conversion->inputContent = "First line\nSecond line";

        $result = $this->mockBinary->execute($conversion);

        $this->assertStringContainsString('<p>First line</p>', $result);
        $this->assertStringContainsString('<p>Second line</p>', $result);
    }

    public function testExecuteReturnsEmptyStringForOnlyEmptyLines(): void
    {
        $conversion = new Conversion('markdown', 'html');
        $conversion->inputContent = "\n\n";

        $result = $this->mockBinary->execute($conversion);

        $this->assertSame('', $result);
    }
}
