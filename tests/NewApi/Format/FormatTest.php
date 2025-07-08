<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests\NewApi\Format;

use Pandoc\Format\InputFormat;
use Pandoc\Format\OutputFormat;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for format enums.
 */
class FormatTest extends TestCase
{
    #[Test]
    public function input_format_can_detect_from_extension(): void
    {
        $this->assertSame(InputFormat::MARKDOWN, InputFormat::fromExtension('md'));
        $this->assertSame(InputFormat::HTML, InputFormat::fromExtension('html'));
        $this->assertSame(InputFormat::DOCX, InputFormat::fromExtension('docx'));
        $this->assertNull(InputFormat::fromExtension('unknown'));
    }

    #[Test]
    public function input_format_supports_extensions(): void
    {
        $this->assertTrue(InputFormat::MARKDOWN->supportsExtension('md'));
        $this->assertTrue(InputFormat::MARKDOWN->supportsExtension('.md'));
        $this->assertTrue(InputFormat::HTML->supportsExtension('html'));
        $this->assertFalse(InputFormat::MARKDOWN->supportsExtension('pdf'));
    }

    #[Test]
    public function input_format_has_display_names(): void
    {
        $this->assertSame('Markdown', InputFormat::MARKDOWN->getDisplayName());
        $this->assertSame('HTML', InputFormat::HTML->getDisplayName());
        $this->assertSame('Word (DOCX)', InputFormat::DOCX->getDisplayName());
    }

    #[Test]
    public function output_format_has_extensions(): void
    {
        $this->assertSame('html', OutputFormat::HTML->getExtension());
        $this->assertSame('pdf', OutputFormat::PDF->getExtension());
        $this->assertSame('docx', OutputFormat::DOCX->getExtension());
        $this->assertSame('md', OutputFormat::MARKDOWN->getExtension());
    }

    #[Test]
    public function output_format_can_detect_from_extension(): void
    {
        $this->assertSame(OutputFormat::HTML, OutputFormat::fromExtension('html'));
        $this->assertSame(OutputFormat::PDF, OutputFormat::fromExtension('pdf'));
        $this->assertSame(OutputFormat::DOCX, OutputFormat::fromExtension('docx'));
        $this->assertNull(OutputFormat::fromExtension('unknown'));
    }

    #[Test]
    public function output_format_can_identify_presentations(): void
    {
        $this->assertTrue(OutputFormat::REVEALJS->isPresentation());
        $this->assertTrue(OutputFormat::BEAMER->isPresentation());
        $this->assertTrue(OutputFormat::PPTX->isPresentation());
        $this->assertFalse(OutputFormat::HTML->isPresentation());
        $this->assertFalse(OutputFormat::PDF->isPresentation());
    }

    #[Test]
    public function output_format_can_identify_latex_requirement(): void
    {
        $this->assertTrue(OutputFormat::PDF->requiresLatex());
        $this->assertTrue(OutputFormat::LATEX->requiresLatex());
        $this->assertTrue(OutputFormat::BEAMER->requiresLatex());
        $this->assertFalse(OutputFormat::HTML->requiresLatex());
        $this->assertFalse(OutputFormat::DOCX->requiresLatex());
    }

    #[Test]
    public function output_format_has_display_names(): void
    {
        $this->assertSame('HTML', OutputFormat::HTML->getDisplayName());
        $this->assertSame('PDF', OutputFormat::PDF->getDisplayName());
        $this->assertSame('Word (DOCX)', OutputFormat::DOCX->getDisplayName());
        $this->assertSame('reveal.js', OutputFormat::REVEALJS->getDisplayName());
    }
}
