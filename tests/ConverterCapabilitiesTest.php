<?php

namespace Pandoc\Tests;

use Pandoc\ConverterCapabilities;
use Pandoc\Format\InputFormat;
use Pandoc\Format\OutputFormat;
use Pandoc\Test\ConverterMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ConverterCapabilities::class)]
class ConverterCapabilitiesTest extends TestCase
{
    #[Test]
    public function itReflectsConverterCapabilities(): void
    {
        $mock = new ConverterMock();
        $caps = new ConverterCapabilities($mock);

        $this->assertSame($mock->getPandocInfo(), $caps->getPandocInfo());
        $this->assertSame($mock->listInputFormats(), $caps->getSupportedInputFormats());
        $this->assertSame($mock->listOutputFormats(), $caps->getSupportedOutputFormats());
        $this->assertSame($mock->listHighlightLanguages(), $caps->getHighlightLanguages());
        $this->assertSame($mock->listHighlightStyles(), $caps->getHighlightStyles());

        $this->assertTrue($caps->supportsInputFormat(InputFormat::MARKDOWN));
        $this->assertFalse($caps->supportsInputFormat(InputFormat::RST) === false);
        $this->assertTrue($caps->supportsOutputFormat(OutputFormat::HTML));
        $this->assertTrue($caps->supportsConversion(InputFormat::MARKDOWN, OutputFormat::HTML));

        $mds = $caps->getInputFormatsForExtension('md');
        $this->assertContains(InputFormat::MARKDOWN, $mds);
        $this->assertEmpty($caps->getInputFormatsForExtension('xyz'));

        $ofs = $caps->getOutputFormatsForExtension('pdf');
        $this->assertContains(OutputFormat::PDF, $ofs);
        $this->assertEmpty($caps->getOutputFormatsForExtension('zzz'));

        $this->assertTrue($caps->hasLatexSupport());

        $summary = $caps->getSummary();
        $this->assertArrayHasKey('pandoc_version', $summary);
        $this->assertArrayHasKey('pdf_support', $summary);
    }
}
