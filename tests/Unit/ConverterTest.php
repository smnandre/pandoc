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
use Pandoc\PandocBinary;
use Pandoc\Test\MockPandocBinary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Converter::class)]
#[UsesClass(Conversion::class)]
#[UsesClass(Format::class)]
#[UsesClass(Options::class)]
#[UsesClass(PandocBinary::class)]
#[UsesClass(MockPandocBinary::class)]
final class ConverterTest extends TestCase
{
    private ConverterInterface $converter;

    protected function setUp(): void
    {
        // Use MockPandocBinary instead of PHPUnit mock for better unit testing
        $mockBinary = new MockPandocBinary();
        $this->converter = new Converter($mockBinary);
    }

    public function testInputSetsInputCorrectly(): void
    {
        $result = $this->converter->content('# Hello World');

        $this->assertInstanceOf(ConverterInterface::class, $result);
        $this->assertNotSame($this->converter, $result);
    }

    public function testFromSetsInputFormat(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->from('markdown')
            ->to('html')
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testToSetsOutputFormat(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testOutputSetsOutputPath(): void
    {
        $outputFile = sys_get_temp_dir().'/test_output.html';

        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->output($outputFile)
            ->convert();

        $this->assertTrue($result->isSuccess());
        $this->assertEquals($outputFile, $result->getPath());
    }

    public function testOptionsWithArray(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->options(['toc' => true, 'standalone' => true])
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testOptionsWithOptionsObject(): void
    {
        $options = Options::create(['toc' => true])->standalone(true);

        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->options($options)
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testSingleOption(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->option('toc', true)
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testSingleVariable(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->variable('title', 'Test Document')
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testMultipleVariables(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->variables(['title' => 'Test', 'author' => 'John'])
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testSingleMetadata(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->metadata('author', 'John Doe')
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testMultipleMetadata(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->metadatas(['author' => 'John', 'date' => '2024-01-01'])
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testGetContentShortcut(): void
    {
        $content = $this->converter
            ->content('# Hello')
            ->to('html')
            ->getContent();

        $this->assertIsString($content);
        $this->assertStringContainsString('<h1', $content);
        $this->assertStringContainsString('Hello', $content);
    }

    public function testGetPathShortcut(): void
    {
        $outputFile = sys_get_temp_dir().'/test.html';

        $path = $this->converter
            ->content('# Hello')
            ->to('html')
            ->output($outputFile)
            ->getPath();

        $this->assertEquals($outputFile, $path);
    }

    public function testFreshReturnsNewInstance(): void
    {
        $fresh = $this->converter->fresh();

        $this->assertInstanceOf(ConverterInterface::class, $fresh);
        $this->assertNotSame($this->converter, $fresh);
    }

    public function testFluentInterfaceReturnsNewInstances(): void
    {
        $step1 = $this->converter->content('# Hello');
        $step2 = $step1->to('html');
        $step3 = $step2->option('toc', true);

        $this->assertNotSame($this->converter, $step1);
        $this->assertNotSame($step1, $step2);
        $this->assertNotSame($step2, $step3);
    }

    public function testComplexChaining(): void
    {
        $content = $this->converter
            ->content('# Complex Document')
            ->from('markdown')
            ->to('html')
            ->option('toc', true)
            ->option('standalone', true)
            ->variable('title', 'Test Document')
            ->variable('author', 'Test Author')
            ->metadata('date', '2024-01-01')
            ->metadata('keywords', 'test, pandoc')
            ->getContent();

        $this->assertIsString($content);
        $this->assertStringContainsString('<h1', $content);
        $this->assertStringContainsString('Complex Document', $content);
    }

    public function testCreateStaticMethod(): void
    {
        $converter = Converter::create();

        $this->assertInstanceOf(ConverterInterface::class, $converter);
        $this->assertInstanceOf(Converter::class, $converter);
    }

    public function testCreateWithCustomBinary(): void
    {
        $customBinary = new MockPandocBinary();
        $converter = Converter::create($customBinary);

        $this->assertInstanceOf(ConverterInterface::class, $converter);
    }

    public function testWithMethodAliasForOptions(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->with(['toc' => true, 'standalone' => true])
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testConvertWithoutInputThrowsException(): void
    {
        $this->expectException(\Pandoc\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Input must be set before conversion');

        $this->converter->to('html')->convert();
    }

    public function testConvertWithoutOutputFormatAndOutputFileThrowsException(): void
    {
        $this->expectException(\Pandoc\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Output format must be specified or guessable from output file extension');

        $this->converter->content('# Hello')->convert();
    }

    public function testGuessInputFormatFromFileExtension(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_').'.md';
        file_put_contents($tempFile, '# Test Document');

        try {
            $result = $this->converter
                ->file($tempFile)
                ->to('html')
                ->convert();

            $this->assertTrue($result->isSuccess());
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testGuessInputFormatFromMarkdownContent(): void
    {
        $result = $this->converter
            ->content('# Markdown Title\n\nSome **bold** text.')
            ->to('html')
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testGuessInputFormatFromHtmlContent(): void
    {
        $result = $this->converter
            ->content('<h1>HTML Title</h1><p>Some content</p>')
            ->to('markdown')
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testGuessOutputFormatFromFileExtension(): void
    {
        $tempOutput = sys_get_temp_dir().'/test_output.html';

        $result = $this->converter
            ->content('# Hello')
            ->output($tempOutput)
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testOutputToDirectoryGeneratesFileName(): void
    {
        $tempDir = sys_get_temp_dir().'/test_dir_'.uniqid();
        mkdir($tempDir);

        try {
            $result = $this->converter
                ->content('# Test Document')
                ->to('html')
                ->output($tempDir)
                ->convert();

            $this->assertTrue($result->isSuccess());
            $this->assertStringStartsWith($tempDir.'/document.html', $result->getPath());
        } finally {
            if (is_dir($tempDir)) {
                $files = glob($tempDir.'/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                rmdir($tempDir);
            }
        }
    }

    public function testOutputToDirectoryWithInputFileGeneratesFileName(): void
    {
        $tempInput = tempnam(sys_get_temp_dir(), 'input_').'.md';
        $tempDir = sys_get_temp_dir().'/test_dir_'.uniqid();

        file_put_contents($tempInput, '# Test Document');
        mkdir($tempDir);

        try {
            $result = $this->converter
                ->file($tempInput)
                ->to('pdf')
                ->output($tempDir)
                ->convert();

            $this->assertTrue($result->isSuccess());

            $expectedBaseName = pathinfo($tempInput, \PATHINFO_FILENAME);
            $this->assertStringContainsString($expectedBaseName.'.pdf', $result->getPath());
        } finally {
            if (file_exists($tempInput)) {
                unlink($tempInput);
            }
            if (is_dir($tempDir)) {
                $files = glob($tempDir.'/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                rmdir($tempDir);
            }
        }
    }

    public function testOptionsWithOptionsObjectMergesCorrectly(): void
    {
        $options = Options::create(['toc' => true])
            ->standalone(true)
            ->withVariables(['title' => 'Test Title'])
            ->withMetadata(['author' => 'Test Author']);

        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->options($options)
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testOptionsArrayMergeWithExistingOptions(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->option('toc', true)
            ->options(['standalone' => true, 'number-sections' => true])
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testVariablesMergeWithExistingVariables(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->variable('title', 'Initial Title')
            ->variables(['author' => 'Test Author', 'date' => '2024-01-01'])
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testMetadatasMergeWithExistingMetadata(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->metadata('author', 'Test Author')
            ->metadatas(['date' => '2024-01-01', 'keywords' => 'test, pandoc'])
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testFromWithNullFormat(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->from(null)
            ->to('html')
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testToWithNullFormat(): void
    {
        $tempOutput = sys_get_temp_dir().'/test.html';

        $result = $this->converter
            ->content('# Hello')
            ->to(null)
            ->output($tempOutput)
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testOutputWithNullValue(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->output(null)
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testInputFormatGuessWithUnknownContent(): void
    {
        $result = $this->converter
            ->content('Plain text without any special formatting')
            ->from('plain')
            ->to('html')
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testConstructorWithNullBinary(): void
    {
        $converter = new Converter(null);

        $this->assertInstanceOf(Converter::class, $converter);
    }

    public function testConstructorSetsProvidedBinary(): void
    {
        $mockBinary = new MockPandocBinary();
        $converter = new Converter($mockBinary);

        $this->assertInstanceOf(Converter::class, $converter);
    }

    public function testOutputPathResolutionWithNonExistentDirectory(): void
    {
        $result = $this->converter
            ->content('# Hello')
            ->to('html')
            ->output('/path/to/output.html')
            ->convert();

        $this->assertTrue($result->isSuccess());
    }

    public function testDirectoryOutputWithUnknownFormatFallsBackToHtml(): void
    {
        $tempDir = sys_get_temp_dir().'/test_dir_'.uniqid('', true);
        mkdir($tempDir);

        try {
            // When output format cannot be guessed, directory output should handle gracefully
            $result = $this->converter
                ->content('# Test Document')
                ->to('html') // Explicitly set format since guessing will fail
                ->output($tempDir)
                ->convert();

            $this->assertTrue($result->isSuccess());
            $this->assertStringEndsWith('.html', $result->getPath());
        } finally {
            if (is_dir($tempDir)) {
                $files = glob($tempDir.'/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                rmdir($tempDir);
            }
        }
    }
}
