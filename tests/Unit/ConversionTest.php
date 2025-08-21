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
use Pandoc\Exception\ConversionFailedException;
use Pandoc\Exception\PandocException;
use Pandoc\Options;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Conversion::class)]
#[UsesClass(Options::class)]
#[UsesClass(ConversionFailedException::class)]
#[UsesClass(PandocException::class)]
final class ConversionTest extends TestCase
{
    public function testNewConstructorSetsProperties(): void
    {
        $options = Options::create(['toc' => true]);
        $conversion = new Conversion(
            inputPath: 'test.md',
            inputFormat: 'markdown',
            outputFormat: 'html',
            outputPath: 'test.html',
            options: $options
        );

        $this->assertSame('test.md', $conversion->inputPath);
        $this->assertSame('markdown', $conversion->inputFormat);
        $this->assertSame('html', $conversion->outputFormat);
        $this->assertSame('test.html', $conversion->outputPath);
        $this->assertSame($options, $conversion->options);
    }

    public function testWithMethodCreatesImmutableCopy(): void
    {
        $original = new Conversion(inputPath: 'original.md');
        $modified = $original->with('output', 'new.html');

        $this->assertSame('original.md', $original->inputPath);
        $this->assertNull($original->output);

        $this->assertSame('original.md', $modified->inputPath);
        $this->assertSame('new.html', $modified->output);
        $this->assertNotSame($original, $modified);
    }

    public function testWithVariableCreatesImmutableCopy(): void
    {
        $original = new Conversion();
        $modified = $original->withVariable('title', 'Test Title');

        $originalVars = $original->options->getVariables();
        $modifiedVars = $modified->options->getVariables();

        $this->assertEmpty($originalVars);
        $this->assertSame(['title' => 'Test Title'], $modifiedVars);
        $this->assertNotSame($original, $modified);
    }

    public function testWithOptionCreatesImmutableCopy(): void
    {
        $original = new Conversion();
        $modified = $original->withOption('number-sections', true);

        $originalOptions = $original->options->getOptions();
        $modifiedOptions = $modified->options->getOptions();

        $this->assertEmpty($originalOptions);
        $this->assertSame(['number-sections' => true], $modifiedOptions);
        $this->assertNotSame($original, $modified);
    }

    public function testToCommandArgsGeneratesCorrectArguments(): void
    {
        $options = Options::create()
            ->toc(true)
            ->standalone(true)
            ->option('template', 'custom.latex')
            ->variable('title', 'Test Document')
            ->variable('author', 'Test Author')
            ->option('number-sections', true)
            ->option('highlight-style', 'github');

        $conversion = new Conversion(
            inputPath: 'test.md',
            inputFormat: 'markdown',
            outputFormat: 'pdf',
            outputPath: 'test.pdf',
            options: $options
        );

        $args = $conversion->toCommandArgs();

        $this->assertContains('--from=markdown', $args);
        $this->assertContains('--to=pdf', $args);
        $this->assertContains('--output=test.pdf', $args);
        $this->assertContains('--toc', $args);
        $this->assertContains('--standalone', $args);
        $this->assertContains('--template=custom.latex', $args);
        $this->assertContains('--variable=title:Test Document', $args);
        $this->assertContains('--variable=author:Test Author', $args);
        $this->assertContains('--number-sections', $args);
        $this->assertContains('--highlight-style=github', $args);
        $this->assertContains('test.md', $args);
    }

    public function testToCommandArgsExcludesOutputForStringOutput(): void
    {
        $conversion = new Conversion(
            inputPath: 'test.md',
            outputFormat: 'html'
        );

        $args = implode(' ', $conversion->toCommandArgs());

        $this->assertStringNotContainsString('--output=', $args);
        $this->assertStringContainsString('--to=html', $args);
    }

    public function testToCommandArgsExcludesInputForStringInput(): void
    {
        $conversion = new Conversion(outputFormat: 'html');
        $conversion->inputContent = '# Test';

        $args = implode(' ', $conversion->toCommandArgs());

        $this->assertStringNotContainsString('# Test', $args);
        $this->assertStringContainsString('--to=html', $args);
    }

    public function testMarkExecutedSetsResultProperties(): void
    {
        $conversion = new Conversion();
        $this->assertFalse($conversion->isExecuted());
        $this->assertFalse($conversion->isSuccess());

        $conversion->markExecuted(
            success: true,
            duration: 1.5,
            outputPath: '/path/to/output.pdf',
            outputContent: '<h1>Test</h1>',
            warnings: ['Warning message']
        );

        $this->assertTrue($conversion->isExecuted());
        $this->assertTrue($conversion->isSuccess());
        $this->assertSame(1.5, $conversion->getDuration());
        $this->assertSame('/path/to/output.pdf', $conversion->getPath());
        $this->assertSame('<h1>Test</h1>', $conversion->getContent());
        $this->assertSame(['Warning message'], $conversion->getWarnings());
    }

    public function testDetectInputFormatFromFileExtension(): void
    {
        $conversion = new Conversion(inputPath: 'document.md');
        $this->assertSame('markdown', $conversion->detectInputFormat());

        $conversion = new Conversion(inputPath: 'document.html');
        $this->assertSame('html', $conversion->detectInputFormat());

        $conversion = new Conversion(inputPath: 'document.docx');
        $this->assertSame('docx', $conversion->detectInputFormat());

        $conversion = new Conversion(inputPath: 'document.unknown');
        $this->assertNull($conversion->detectInputFormat());
    }

    public function testDetectOutputFormatFromFileExtension(): void
    {
        $conversion = new Conversion(outputPath: 'document.pdf');
        $this->assertSame('pdf', $conversion->detectOutputFormat());

        $conversion = new Conversion(outputPath: 'document.html');
        $this->assertSame('html', $conversion->detectOutputFormat());

        $conversion = new Conversion(outputPath: 'document.docx');
        $this->assertSame('docx', $conversion->detectOutputFormat());
    }

    public function testDetectOutputFormatReturnsNullForStringOutput(): void
    {
        $conversion = new Conversion();
        $this->assertNull($conversion->detectOutputFormat());
    }

    public function testIsStringInputAndOutputDetection(): void
    {
        $stringInput = new Conversion();
        $stringInput->inputContent = '# Test';
        $this->assertTrue($stringInput->isStringInput());

        $fileInput = new Conversion(inputPath: 'test.md');
        $this->assertFalse($fileInput->isStringInput());

        $stringOutput = new Conversion();
        $this->assertTrue($stringOutput->isStringOutput());

        $fileOutput = new Conversion(outputPath: 'test.html');
        $this->assertFalse($fileOutput->isStringOutput());
    }

    public function testGetContentThrowsExceptionWhenNotExecuted(): void
    {
        $conversion = new Conversion();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Conversion not executed yet');

        $conversion->getContent();
    }

    public function testGetContentThrowsExceptionWhenFailed(): void
    {
        $conversion = new Conversion();
        $conversion->markExecuted(false, 1.0, error: 'Conversion failed');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Conversion failed');

        $conversion->getContent();
    }

    public function testIsConfiguredReturnsTrueWhenProperlyConfigured(): void
    {
        $conversion = new Conversion(inputPath: 'test.md', outputPath: 'test.html');
        $this->assertTrue($conversion->isConfigured());

        $stringConversion = new Conversion();
        $stringConversion->inputContent = '# Test';
        $this->assertTrue($stringConversion->isConfigured());
    }

    public function testValidateThrowsExceptionForUnconfiguredConversion(): void
    {
        $conversion = new Conversion();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Conversion not properly configured');

        $conversion->validate();
    }

    public function testValidateThrowsExceptionForNonexistentInputFile(): void
    {
        $conversion = new Conversion(inputPath: 'nonexistent.md', outputPath: 'test.html');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Input file not found');

        $conversion->validate();
    }

    public function testValidateThrowsExceptionForNonexistentOutputDirectory(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, '# Test');

        try {
            $conversion = new Conversion(inputPath: $tempFile, outputPath: '/nonexistent/dir/test.html');

            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Output directory not found');

            $conversion->validate();
        } finally {
            unlink($tempFile);
        }
    }

    public function testGetInputPath(): void
    {
        $conversion = new Conversion();
        $conversion->inputPath = '/foo/bar.md';

        $this->assertSame('/foo/bar.md', $conversion->getInputPath());
    }

    public function testGetInputContent(): void
    {
        $conversion = new Conversion();
        $conversion->inputContent = '# Test Content';

        $this->assertSame('# Test Content', $conversion->getInputContent());
    }

    public function testGetInputContentReturnsNull(): void
    {
        $conversion = new Conversion(inputPath: 'file.md');

        $this->assertNull($conversion->getInputContent());
    }

    public function testWithMethodCreatesNewInstanceWithModifiedProperty(): void
    {
        $conversion = new Conversion(inputPath: 'original.md');
        $newConversion = $conversion->with('outputFormat', 'html');

        $this->assertNotSame($conversion, $newConversion);
        $this->assertSame('html', $newConversion->outputFormat);
        $this->assertNull($conversion->outputFormat);
    }

    public function testWithMethodIgnoresInvalidProperty(): void
    {
        $conversion = new Conversion(inputPath: 'test.md');
        $newConversion = $conversion->with('nonExistentProperty', 'value');

        $this->assertNotSame($conversion, $newConversion);
    }

    public function testWithVariableMethod(): void
    {
        $conversion = new Conversion();
        $newConversion = $conversion->withVariable('title', 'Test Document');

        $this->assertNotSame($conversion, $newConversion);
        $this->assertSame(['title' => 'Test Document'], $newConversion->options->getVariables());
        $this->assertEmpty($conversion->options->getVariables());
    }

    public function testWithOptionMethod(): void
    {
        $conversion = new Conversion();
        $newConversion = $conversion->withOption('toc', true);

        $this->assertNotSame($conversion, $newConversion);
        $this->assertSame(['toc' => true], $newConversion->options->getOptions());
        $this->assertEmpty($conversion->options->getOptions());
    }

    public function testWithOptionMethodWithDefaultValue(): void
    {
        $conversion = new Conversion();
        $newConversion = $conversion->withOption('standalone');

        $this->assertSame(['standalone' => true], $newConversion->options->getOptions());
    }

    public function testToCommandArgsWithFalseOptionValues(): void
    {
        $options = Options::create([
            'toc' => false,
            'standalone' => true,
            'number-sections' => null,
        ]);

        $conversion = new Conversion(outputFormat: 'html', options: $options);

        $args = $conversion->toCommandArgs();

        $this->assertContains('--standalone', $args);
        $this->assertNotContains('--toc', $args);
        $this->assertNotContains('--number-sections', $args);
    }

    public function testToCommandArgsWithScalarValues(): void
    {
        $options = Options::create([
            'dpi' => 300,
            'margin-top' => 1.5,
            'template' => 'custom',
        ]);

        $conversion = new Conversion(outputFormat: 'html', options: $options);

        $args = $conversion->toCommandArgs();

        $this->assertContains('--dpi=300', $args);
        $this->assertContains('--margin-top=1.5', $args);
        $this->assertContains('--template=custom', $args);
    }

    public function testToCommandArgsWithEmptyStringValue(): void
    {
        $options = Options::create(['empty-option' => '']);
        $conversion = new Conversion(outputFormat: 'html', options: $options);

        $args = $conversion->toCommandArgs();

        $this->assertNotContains('--empty-option=', $args);
    }

    public function testIsConfiguredWithInputContentAndStringOutput(): void
    {
        $conversion = new Conversion(outputFormat: 'html');
        $conversion->inputContent = '# Test';

        $this->assertTrue($conversion->isConfigured());
    }

    public function testIsConfiguredWithInputFileAndOutputPath(): void
    {
        $conversion = new Conversion(
            inputPath: 'input.md',
            outputPath: 'output.html'
        );

        $this->assertTrue($conversion->isConfigured());
    }

    public function testIsConfiguredWithMissingInput(): void
    {
        $conversion = new Conversion(outputFormat: 'html');

        $this->assertFalse($conversion->isConfigured());
    }

    public function testIsConfiguredWithMissingInputAndOutput(): void
    {
        $conversion = new Conversion();

        $this->assertFalse($conversion->isConfigured());
    }

    public function testIsStringInputReturnsTrueWhenInputContentSet(): void
    {
        $conversion = new Conversion();
        $conversion->inputContent = '# Test';

        $this->assertTrue($conversion->isStringInput());
    }

    public function testIsStringInputReturnsFalseWhenNoInputContent(): void
    {
        $conversion = new Conversion(inputPath: 'file.md');

        $this->assertFalse($conversion->isStringInput());
    }

    public function testIsStringOutputReturnsTrueWhenNoOutputPath(): void
    {
        $conversion = new Conversion(outputFormat: 'html');

        $this->assertTrue($conversion->isStringOutput());
    }

    public function testIsStringOutputReturnsFalseWhenOutputPathSet(): void
    {
        $conversion = new Conversion(outputPath: 'output.html');

        $this->assertFalse($conversion->isStringOutput());
    }

    public function testGetOutputPathThrowsExceptionWhenNotExecuted(): void
    {
        $conversion = new Conversion();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Conversion not executed yet');

        $conversion->getOutputPath();
    }

    public function testGetOutputPathThrowsExceptionWhenConversionFailed(): void
    {
        $conversion = new Conversion();
        $conversion->markExecuted(
            success: false,
            duration: 0.1,
            error: 'Conversion failed'
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Conversion failed');

        $conversion->getOutputPath();
    }

    public function testGetErrorReturnsNullWhenNoError(): void
    {
        $conversion = new Conversion();
        $conversion->markExecuted(success: true, duration: 0.1);

        $this->assertNull($conversion->getError());
    }

    public function testGetErrorReturnsErrorMessage(): void
    {
        $conversion = new Conversion();
        $conversion->markExecuted(
            success: false,
            duration: 0.1,
            error: 'Test error message'
        );

        $this->assertSame('Test error message', $conversion->getError());
    }

    public function testGetWarningsReturnsEmptyArrayByDefault(): void
    {
        $conversion = new Conversion();

        $this->assertSame([], $conversion->getWarnings());
    }

    public function testValidateThrowsExceptionWhenNotConfigured(): void
    {
        $conversion = new Conversion();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Conversion not properly configured');

        $conversion->validate();
    }

    public function testValidateThrowsExceptionWhenInputFileNotFound(): void
    {
        $conversion = new Conversion(
            inputPath: '/nonexistent/file.md',
            outputFormat: 'html'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Input file not found');

        $conversion->validate();
    }

    public function testValidateThrowsExceptionWhenOutputDirectoryNotFound(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');

        try {
            $conversion = new Conversion(
                inputPath: $tempFile,
                outputPath: '/nonexistent/directory/output.html'
            );

            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('Output directory not found');

            $conversion->validate();
        } finally {
            unlink($tempFile);
        }
    }

    public function testValidatePassesWithValidConfiguration(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        $outputDir = sys_get_temp_dir();

        try {
            $conversion = new Conversion(
                inputPath: $tempFile,
                outputPath: $outputDir.'/output.html'
            );

            $conversion->validate();
            $this->assertTrue(true);
        } finally {
            unlink($tempFile);
        }
    }

    public function testValidatePassesWithStringInput(): void
    {
        $conversion = new Conversion(outputFormat: 'html');
        $conversion->inputContent = '# Test';

        $conversion->validate();
        $this->assertTrue(true);
    }

    public function testDetectInputFormatReturnsNullForNoInput(): void
    {
        $conversion = new Conversion();

        $this->assertNull($conversion->detectInputFormat());
    }

    public function testDetectInputFormatReturnsExistingFormat(): void
    {
        $conversion = new Conversion(inputFormat: 'markdown');

        $this->assertSame('markdown', $conversion->detectInputFormat());
    }

    public function testAllFileExtensionDetections(): void
    {
        $extensions = [
            'md' => 'markdown',
            'markdown' => 'markdown',
            'html' => 'html',
            'htm' => 'html',
            'docx' => 'docx',
            'odt' => 'odt',
            'tex' => 'latex',
            'rst' => 'rst',
            'txt' => 'plain',
        ];

        foreach ($extensions as $ext => $expectedFormat) {
            $conversion = new Conversion(inputPath: "document.{$ext}");
            $this->assertSame($expectedFormat, $conversion->detectInputFormat());
        }

        $outputExtensions = [
            'html' => 'html',
            'htm' => 'html',
            'pdf' => 'pdf',
            'docx' => 'docx',
            'odt' => 'odt',
            'tex' => 'latex',
            'md' => 'markdown',
            'markdown' => 'markdown',
            'rst' => 'rst',
            'txt' => 'plain',
            'epub' => 'epub',
        ];

        foreach ($outputExtensions as $ext => $expectedFormat) {
            $conversion = new Conversion(outputPath: "document.{$ext}");
            $this->assertSame($expectedFormat, $conversion->detectOutputFormat());
        }
    }

    public function testLegacyConfigArraySupport(): void
    {
        $config = [
            'inputPath' => 'test.md',
            'outputPath' => 'test.html',
            'tableOfContents' => true,
            'standalone' => true,
            'template' => 'custom.latex',
            'variables' => ['title' => 'Test Document'],
            'options' => ['number-sections' => true],
        ];

        $conversion = new Conversion(config: $config);

        $this->assertSame('test.md', $conversion->inputPath);
        $this->assertSame('test.html', $conversion->outputPath);

        $options = $conversion->options->toArray();
        $this->assertTrue($options['options']['toc']);
        $this->assertTrue($options['options']['standalone']);
        $this->assertSame('custom.latex', $options['options']['template']);
        $this->assertSame(['title' => 'Test Document'], $options['variables']);
        $this->assertTrue($options['options']['number-sections']);
    }

    public function testLegacyInputPropertySupport(): void
    {
        $config = ['input' => 'legacy.md'];
        $conversion = new Conversion(config: $config);

        $this->assertSame('legacy.md', $conversion->inputPath);
    }
}
