<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests\Unit;

use Pandoc\Format;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Format::class)]
final class FormatTest extends TestCase
{
    public function testFromFileDetectsCorrectFormat(): void
    {
        $this->assertSame(Format::MARKDOWN, Format::fromFile('document.md'));
        $this->assertSame(Format::HTML, Format::fromFile('page.html'));
        $this->assertSame(Format::PDF, Format::fromFile('report.pdf'));
        $this->assertSame(Format::DOCX, Format::fromFile('letter.docx'));
        $this->assertNull(Format::fromFile('unknown.xyz'));
    }

    public function testGetExtension(): void
    {
        $this->assertSame('md', Format::MARKDOWN->getExtension());
        $this->assertSame('html', Format::HTML->getExtension());
        $this->assertSame('pdf', Format::PDF->getExtension());
        $this->assertSame('docx', Format::DOCX->getExtension());
    }

    #[DataProvider('descriptionProvider')]
    public function testGetDescription(Format $format, string $expectedDescription): void
    {
        self::assertSame($expectedDescription, $format->getDescription());
    }

    public static function descriptionProvider(): iterable
    {
        yield 'Markdown' => [Format::MARKDOWN, 'Markdown'];
        yield 'HTML' => [Format::HTML, 'HTML'];
        yield 'DOCX' => [Format::DOCX, 'Microsoft Word (DOCX)'];
        yield 'PDF' => [Format::PDF, 'PDF'];
        yield 'Typst' => [Format::TYPST, 'Typst'];
    }

    #[DataProvider('tocSupportProvider')]
    public function testSupportsToc(Format $format, bool $expected): void
    {
        self::assertSame($expected, $format->supportsToc());
    }

    public static function tocSupportProvider(): iterable
    {
        yield 'Markdown does not support TOC' => [Format::MARKDOWN, false];
        yield 'HTML supports TOC' => [Format::HTML, true];
        yield 'DOCX supports TOC' => [Format::DOCX, true];
        yield 'PDF supports TOC' => [Format::PDF, true];
        yield 'RST does not support TOC' => [Format::RST, false];
        yield 'JSON does not support TOC' => [Format::JSON, false];
    }

    #[DataProvider('standaloneProvider')]
    public function testRequiresStandalone(Format $format, bool $expected): void
    {
        self::assertSame($expected, $format->requiresStandalone());
    }

    public static function standaloneProvider(): iterable
    {
        yield 'Markdown does not require standalone' => [Format::MARKDOWN, false];
        yield 'DocBook requires standalone' => [Format::DOCBOOK, true];
        yield 'PDF requires standalone' => [Format::PDF, true];
        yield 'JSON does not require standalone' => [Format::JSON, false];
        yield 'RevealJS requires standalone' => [Format::REVEALJS, true];
    }

    #[DataProvider('inputFormatProvider')]
    public function testIsInputFormat(Format $format, bool $expected): void
    {
        self::assertSame($expected, $format->isInputFormat());
    }

    public static function inputFormatProvider(): iterable
    {
        yield 'Markdown is an input format' => [Format::MARKDOWN, true];
        yield 'PDF is not an input format' => [Format::PDF, false];
        yield 'Slidy is not an input format' => [Format::SLIDY, false];
        yield 'DOCX is an input format' => [Format::DOCX, true];
        yield 'JSON is an input format' => [Format::JSON, true];
    }

    public function testFormatSupportsOutput(): void
    {
        self::assertTrue(Format::MARKDOWN->isOutputFormat());
    }

    public function testInputFormatsReturnsOnlyInputFormats(): void
    {
        $inputFormats = Format::inputFormats();
        self::assertContains(Format::MARKDOWN, $inputFormats);
        self::assertNotContains(Format::PDF, $inputFormats);
    }

    public function testOutputFormatsReturnsAllFormats(): void
    {
        $outputFormats = Format::outputFormats();
        self::assertContains(Format::MARKDOWN, $outputFormats);
        self::assertContains(Format::PDF, $outputFormats);
    }

    #[DataProvider('categoryProvider')]
    public function testGetByCategoryReturnsExpectedFormats(string $category, array $expectedFormats): void
    {
        $formats = Format::getByCategory($category);
        self::assertSame($expectedFormats, $formats);
    }

    public static function categoryProvider(): iterable
    {
        yield 'text' => [
            'text',
            [Format::MARKDOWN, Format::COMMONMARK, Format::GFM, Format::PLAIN, Format::RST],
        ];
        yield 'web' => [
            'web',
            [Format::HTML, Format::HTML4, Format::HTML5, Format::XHTML],
        ];
        yield 'document' => [
            'document',
            [Format::DOCX, Format::ODT, Format::RTF, Format::PDF],
        ];
        yield 'ebook' => [
            'ebook',
            [Format::EPUB, Format::EPUB2, Format::EPUB3, Format::FB2],
        ];
        yield 'presentation' => [
            'presentation',
            [Format::REVEALJS, Format::SLIDY, Format::PPTX, Format::BEAMER],
        ];
        yield 'academic' => [
            'academic',
            [Format::LATEX, Format::BEAMER, Format::DOCBOOK, Format::JATS, Format::TEI],
        ];
        yield 'unknown' => [
            'unknown',
            [],
        ];
    }
}
