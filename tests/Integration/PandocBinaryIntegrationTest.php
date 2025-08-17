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
use Pandoc\Options;
use Pandoc\PandocBinary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PandocBinary::class)]
#[UsesClass(Conversion::class)]
#[UsesClass(Options::class)]
final class PandocBinaryIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        if (!PandocBinary::isInstalled()) {
            $this->markTestSkipped('Pandoc not installed for integration test');
        }
    }

    public function testCreateReturnsDefaultInstance(): void
    {
        $binary1 = PandocBinary::create();
        $binary2 = PandocBinary::create();

        $this->assertSame($binary1, $binary2);
    }

    public function testCreateStaticInstanceCaching(): void
    {
        $reflection = new \ReflectionClass(PandocBinary::class);
        $property = $reflection->getProperty('defaultInstance');
        $property->setValue(null, null);

        $instance1 = PandocBinary::create();
        $instance2 = PandocBinary::create();

        $this->assertSame($instance1, $instance2);
    }

    public function testIsValidPandocBinaryWithValidPath(): void
    {
        $reflection = new \ReflectionClass(PandocBinary::class);
        $method = $reflection->getMethod('isValidPandocBinary');

        $validPath = PandocBinary::create()->getBinaryPath();
        $result = $method->invoke(null, $validPath);
        $this->assertTrue($result);
    }

    public function testExecuteWithValidBinary(): void
    {
        $binary = PandocBinary::create();
        $conversion = new Conversion(
            inputFormat: 'markdown',
            outputFormat: 'html'
        );
        $conversion->inputContent = '# Test Document';

        $result = $binary->execute($conversion);

        $this->assertIsString($result);
        $this->assertStringContainsString('<h1', $result);
        $this->assertStringContainsString('Test Document', $result);
    }

    public function testGetVersionWithValidBinary(): void
    {
        $binary = PandocBinary::create();
        $version = $binary->getVersion();

        $this->assertIsString($version);
        $this->assertMatchesRegularExpression('/^\d+\.\d+/', $version);
    }

    public function testGetInputFormatsWithValidBinary(): void
    {
        $binary = PandocBinary::create();
        $formats = $binary->getInputFormats();

        $this->assertIsArray($formats);
        $this->assertContains('markdown', $formats);
        $this->assertContains('html', $formats);
    }

    public function testGetOutputFormatsWithValidBinary(): void
    {
        $binary = PandocBinary::create();
        $formats = $binary->getOutputFormats();

        $this->assertIsArray($formats);
        $this->assertContains('html', $formats);
        $this->assertContains('markdown', $formats);
    }

    public function testGetHighlightLanguagesWithValidBinary(): void
    {
        $binary = PandocBinary::create();
        $languages = $binary->getHighlightLanguages();

        $this->assertIsArray($languages);
    }

    public function testGetHighlightStylesWithValidBinary(): void
    {
        $binary = PandocBinary::create();
        $styles = $binary->getHighlightStyles();

        $this->assertIsArray($styles);
    }

    public function testSupportsWithValidFormats(): void
    {
        $binary = PandocBinary::create();

        $inputFormats = $binary->getInputFormats();
        $outputFormats = $binary->getOutputFormats();

        $candidatePairs = [];
        if (\in_array('markdown', $inputFormats, true) && \in_array('html', $outputFormats, true)) {
            $candidatePairs[] = ['markdown', 'html'];
        }
        if (\in_array('html', $inputFormats, true) && \in_array('markdown', $outputFormats, true)) {
            $candidatePairs[] = ['html', 'markdown'];
        }

        if ([] === $candidatePairs) {
            $this->markTestSkipped('No valid input/output format pairs found for testing');
        }

        foreach ($candidatePairs as [$from, $to]) {
            $this->assertTrue($binary->supports($from, $to), "Expected support for conversion from $from to $to");
        }
    }

    public function testIsInstalledDetection(): void
    {
        $result = PandocBinary::isInstalled();
        $this->assertTrue($result);
    }

    public function testConvertWithRealBinary(): void
    {
        $binary = PandocBinary::create();
        $conversion = new Conversion(
            outputFormat: 'html'
        );
        $conversion->inputContent = '# Hello World';

        $result = $binary->convert($conversion);

        $this->assertTrue($result->isSuccess());
        $this->assertGreaterThan(0, $result->getDuration());
        $this->assertStringContainsString('<h1', $result->getContent());
    }

    public function testRunListCommandSuccessfulExecution(): void
    {
        $binary = PandocBinary::create();
        $reflection = new \ReflectionClass($binary);
        $method = $reflection->getMethod('runListCommand');

        $result = $method->invoke($binary, '--list-input-formats', 'Failed to get input formats');

        $this->assertIsArray($result);
        $this->assertContains('markdown', $result);
        $this->assertContains('html', $result);
    }
}
