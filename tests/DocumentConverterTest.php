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

use Pandoc\BatchConverter;
use Pandoc\Configuration\ConversionOptions;
use Pandoc\ConverterCapabilities;
use Pandoc\DocumentConverter;
use Pandoc\Format\InputFormat;
use Pandoc\Format\OutputFormat;
use Pandoc\IO\InputSource;
use Pandoc\IO\OutputTarget;
use Pandoc\Result\ConversionResult;
use Pandoc\Test\ConverterMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the new DocumentConverter API.
 */
#[CoversClass(DocumentConverter::class)]
class DocumentConverterTest extends TestCase
{
    #[Test]
    public function it_can_be_created(): void
    {
        $converter = DocumentConverter::create();
        $this->assertInstanceOf(DocumentConverter::class, $converter);
    }

    #[Test]
    public function it_can_convert_string_content(): void
    {
        $converter = DocumentConverter::create(new ConverterMock());

        $input = InputSource::string('# Hello World', InputFormat::MARKDOWN);
        $output = OutputTarget::string();
        $format = OutputFormat::HTML;

        $result = $converter->convert($input, $output, $format);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertTrue($result->isStringResult());
        $this->assertNotNull($result->getContent());
    }

    #[Test]
    public function it_can_convert_with_options(): void
    {
        $converter = DocumentConverter::create(new ConverterMock());

        $input = InputSource::string('# Hello World', InputFormat::MARKDOWN);
        $output = OutputTarget::string();
        $format = OutputFormat::HTML;
        $options = ConversionOptions::create()
            ->tableOfContents()
            ->numberSections();

        $result = $converter->convert($input, $output, $format, $options);

        $this->assertInstanceOf(ConversionResult::class, $result);
        $this->assertTrue($result->isSuccessful());
    }

    #[Test]
    public function it_provides_batch_converter(): void
    {
        $converter = DocumentConverter::create(new ConverterMock());
        $batch = $converter->batch();

        $this->assertInstanceOf(BatchConverter::class, $batch);
    }

    #[Test]
    public function it_provides_capabilities(): void
    {
        $converter = DocumentConverter::create(new ConverterMock());
        $capabilities = $converter->getCapabilities();

        $this->assertInstanceOf(ConverterCapabilities::class, $capabilities);
    }
}
