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
use Pandoc\Exception\PandocNotInstalledException;
use Pandoc\Options;
use Pandoc\PandocBinary;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PandocBinary::class)]
#[UsesClass(PandocException::class)]
#[UsesClass(PandocNotInstalledException::class)]
#[UsesClass(ConversionFailedException::class)]
#[UsesClass(Conversion::class)]
#[UsesClass(Options::class)]
final class PandocBinaryTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $binary = new PandocBinary('/usr/bin/pandoc');

        $this->assertSame('/usr/bin/pandoc', $binary->getBinaryPath());
    }

    public function testConstructorWithCapabilities(): void
    {
        $capabilities = [
            'version' => '2.18',
            'binary_path' => '/usr/bin/pandoc',
            'input_formats' => ['markdown', 'html'],
            'output_formats' => ['html', 'pdf'],
            'highlight_languages' => ['php', 'python'],
            'highlight_styles' => ['tango', 'pygments'],
            'loaded_at' => time(),
        ];

        $binary = new PandocBinary('/usr/bin/pandoc', $capabilities);
        $result = $binary->getCapabilities();

        $this->assertSame($capabilities, $result);
    }

    public function testConstructorWithNullCapabilities(): void
    {
        $binary = new PandocBinary('/fake/pandoc', null);

        $reflection = new \ReflectionClass($binary);
        $property = $reflection->getProperty('capabilities');

        $this->assertSame([], $property->getValue($binary));
    }

    public function testFromPathWithNonExecutableThrowsException(): void
    {
        $this->expectException(PandocNotInstalledException::class);
        $this->expectExceptionMessage('Pandoc binary not executable: /nonexistent/path');

        PandocBinary::fromPath('/nonexistent/path');
    }

    public function testFromPathWithValidPath(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pandoc_test');
        chmod($tempFile, 0755);

        try {
            $binary = PandocBinary::fromPath($tempFile);
            $this->assertSame($tempFile, $binary->getBinaryPath());
        } finally {
            unlink($tempFile);
        }
    }

    public function testFromPathCreatesBinaryInstance(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'pandoc_test');
        chmod($tempFile, 0755);

        try {
            $binary = PandocBinary::fromPath($tempFile);
            $this->assertSame($tempFile, $binary->getBinaryPath());
        } finally {
            unlink($tempFile);
        }
    }

    public function returnsPathWhenValidPandocBinaryExistsInAdditionalPaths(): void
    {
        $mockedPaths = ['/usr/local/bin/pandoc', '/invalid/path/pandoc'];
        $mockedValidPath = '/usr/local/bin/pandoc';

        $mock = $this->getMockBuilder(PandocBinary::class)
            ->onlyMethods(['getAdditionalPaths', 'isValidPandocBinary'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getAdditionalPaths')
            ->willReturn($mockedPaths);

        $mock->expects($this->exactly(2))
            ->method('isValidPandocBinary')
            ->withConsecutive(['/usr/local/bin/pandoc'], ['/invalid/path/pandoc'])
            ->willReturnOnConsecutiveCalls(true, false);

        $result = $this->invokeMethod($mock, 'detectBinary');
        $this->assertSame($mockedValidPath, $result);
    }

    public function throwsExceptionWhenNoValidPandocBinaryExists(): void
    {
        $mockedPaths = ['/invalid/path1/pandoc', '/invalid/path2/pandoc'];

        $mock = $this->getMockBuilder(PandocBinary::class)
            ->onlyMethods(['getAdditionalPaths', 'isValidPandocBinary'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getAdditionalPaths')
            ->willReturn($mockedPaths);

        $mock->expects($this->exactly(2))
            ->method('isValidPandocBinary')
            ->withConsecutive(['/invalid/path1/pandoc'], ['/invalid/path2/pandoc'])
            ->willReturn(false);

        $this->expectException(PandocNotInstalledException::class);
        $this->expectExceptionMessage('Pandoc not found. Please install from https://pandoc.org/installing.html');

        $this->invokeMethod($mock, 'detectBinary');
    }

    public function testConvertWithRealBinaryFailure(): void
    {
        $binary = new PandocBinary('/fake/pandoc');
        $conversion = new Conversion(
            outputFormat: 'html'
        );
        $conversion->inputContent = '# Test';

        $result = $binary->convert($conversion);

        $this->assertFalse($result->isSuccess());
        $this->assertNotNull($result->getError());
    }

    public function testExecuteWithRealBinaryFailure(): void
    {
        $binary = new PandocBinary('/fake/pandoc');
        $conversion = new Conversion(
            outputFormat: 'html'
        );
        $conversion->inputContent = '# Test';

        $this->expectException(ConversionFailedException::class);
        $this->expectExceptionMessage('Pandoc conversion failed');

        $binary->execute($conversion);
    }

    public function testConversionWithInvalidInputFormatDetection(): void
    {
        $binary = new PandocBinary('/fake/pandoc');
        $conversion = new Conversion(
            outputFormat: 'html'
        );
        $conversion->inputContent = '# Test';

        $result = $binary->convert($conversion);

        $this->assertFalse($result->isSuccess());
        $this->assertNotNull($result->getError());
        $this->assertIsFloat($result->getDuration());
    }

    public function testGetVersionWithInvalidBinary(): void
    {
        $binary = new PandocBinary('/fake/pandoc');

        $this->expectException(PandocException::class);
        $this->expectExceptionMessage('Failed to get pandoc version');

        $binary->getVersion();
    }

    public function testGetInputFormatsWithInvalidBinary(): void
    {
        $binary = new PandocBinary('/fake/pandoc');

        $this->expectException(PandocException::class);
        $this->expectExceptionMessage('Failed to get input formats');

        $binary->getInputFormats();
    }

    public function testGetOutputFormatsWithInvalidBinary(): void
    {
        $binary = new PandocBinary('/fake/pandoc');

        $this->expectException(PandocException::class);
        $this->expectExceptionMessage('Failed to get output formats');

        $binary->getOutputFormats();
    }

    public function testGetHighlightLanguagesWithInvalidBinary(): void
    {
        $binary = new PandocBinary('/fake/pandoc');

        $languages = $binary->getHighlightLanguages();
        $this->assertSame([], $languages);
    }

    public function testGetHighlightStylesWithInvalidBinary(): void
    {
        $binary = new PandocBinary('/fake/pandoc');

        $styles = $binary->getHighlightStyles();
        $this->assertSame([], $styles);
    }

    public function testSupportsReturnsFalseForEmptyCapabilities(): void
    {
        $capabilities = [
            'version' => null,
            'binary_path' => '/fake/pandoc',
            'input_formats' => [],
            'output_formats' => [],
            'highlight_languages' => [],
            'highlight_styles' => [],
            'loaded_at' => time(),
        ];

        $binary = new PandocBinary('/fake/pandoc', $capabilities);

        $this->assertFalse($binary->supports('markdown', 'html'));
    }

    public function testSupportsReturnsTrueForValidFormats(): void
    {
        $capabilities = [
            'version' => '2.18',
            'binary_path' => '/fake/pandoc',
            'input_formats' => ['markdown', 'html'],
            'output_formats' => ['html', 'pdf'],
            'highlight_languages' => [],
            'highlight_styles' => [],
            'loaded_at' => time(),
        ];

        $binary = new PandocBinary('/fake/pandoc', $capabilities);

        $this->assertTrue($binary->supports('markdown', 'html'));
        $this->assertTrue($binary->supports('html', 'pdf'));
        $this->assertFalse($binary->supports('markdown', 'docx'));
        $this->assertFalse($binary->supports('rst', 'html'));
    }

    public function testSupportsWithMalformedCapabilities(): void
    {
        $capabilities = [
            'version' => '2.18',
            'binary_path' => '/fake/pandoc',
            'input_formats' => 'not-an-array',
            'output_formats' => ['html'],
            'highlight_languages' => [],
            'highlight_styles' => [],
            'loaded_at' => time(),
        ];

        $binary = new PandocBinary('/fake/pandoc', $capabilities);

        $this->expectException(\TypeError::class);
        $binary->supports('markdown', 'html');
    }

    public function testGetCapabilitiesWithPreloadedCapabilities(): void
    {
        $capabilities = [
            'version' => '2.19.2',
            'binary_path' => '/test/pandoc',
            'input_formats' => ['markdown', 'html'],
            'output_formats' => ['html', 'pdf'],
            'highlight_languages' => ['php', 'python'],
            'highlight_styles' => ['tango', 'pygments'],
            'loaded_at' => time(),
        ];

        $binary = new PandocBinary('/test/pandoc', $capabilities);
        $result = $binary->getCapabilities();

        $this->assertSame($capabilities, $result);
    }

    public function testLoadCapabilitiesWithInvalidBinary(): void
    {
        $binary = new PandocBinary('/fake/pandoc');
        $capabilities = $binary->getCapabilities();

        $this->assertNull($capabilities['version']);
        $this->assertSame('/fake/pandoc', $capabilities['binary_path']);
        $this->assertSame([], $capabilities['input_formats']);
        $this->assertSame([], $capabilities['output_formats']);
        $this->assertSame([], $capabilities['highlight_languages']);
        $this->assertSame([], $capabilities['highlight_styles']);
        $this->assertArrayHasKey('error', $capabilities);
        $this->assertIsInt($capabilities['loaded_at']);
    }

    public function testLoadCapabilitiesWithExceptionInError(): void
    {
        $binary = new PandocBinary('/fake/pandoc');
        $capabilities = $binary->getCapabilities();

        $this->assertArrayHasKey('error', $capabilities);
        $this->assertIsString($capabilities['error']);
        $this->assertNotEmpty($capabilities['error']);
    }

    public function testCapabilitiesCachingBehavior(): void
    {
        $binary = new PandocBinary('/fake/pandoc');

        $capabilities1 = $binary->getCapabilities();
        $capabilities2 = $binary->getCapabilities();

        $this->assertSame($capabilities1, $capabilities2);
    }

    public function testVersionParsingWithInvalidOutput(): void
    {
        $versionOutput = 'some invalid output without version';
        $pattern = '/pandoc\s+([\d.]+)/';

        $matches = [];
        $result = preg_match($pattern, $versionOutput, $matches);

        $this->assertSame(0, $result);
        $this->assertEmpty($matches);
    }

    public function testVersionParsingWithValidOutput(): void
    {
        $versionOutput = "pandoc 2.19.2\nCompiled with pandoc-types 1.22.2.1";
        $pattern = '/pandoc\s+([\d.]+)/';

        $matches = [];
        $result = preg_match($pattern, $versionOutput, $matches);

        $this->assertSame(1, $result);
        $this->assertSame('2.19.2', $matches[1]);
    }

    #[RequiresOperatingSystemFamily('Windows')]
    public function testGetAdditionalPathsReturnsWindowsPaths(): void
    {
        $reflection = new \ReflectionClass(PandocBinary::class);
        $method = $reflection->getMethod('getAdditionalPaths');

        $paths = $method->invoke(null);

        $this->assertIsArray($paths);
        $this->assertContains('C:\\Program Files\\Pandoc\\pandoc.exe', $paths);
        $this->assertContains('C:\\Program Files (x86)\\Pandoc\\pandoc.exe', $paths);
    }

    #[RequiresOperatingSystemFamily('Windows')]
    public function testGetAdditionalPathsWithMissingUserProfile(): void
    {
        $originalUserProfile = getenv('USERPROFILE');
        putenv('USERPROFILE=');

        try {
            $reflection = new \ReflectionClass(PandocBinary::class);
            $method = $reflection->getMethod('getAdditionalPaths');

            $paths = $method->invoke(null);

            $this->assertIsArray($paths);
            $this->assertContains('C:\\Program Files\\Pandoc\\pandoc.exe', $paths);
        } finally {
            putenv($originalUserProfile ? "USERPROFILE=$originalUserProfile" : 'USERPROFILE');
        }
    }

    #[RequiresOperatingSystemFamily('Darwin')]
    public function testGetAdditionalPathsReturnsDarwinPaths(): void
    {
        $reflection = new \ReflectionClass(PandocBinary::class);
        $method = $reflection->getMethod('getAdditionalPaths');

        $paths = $method->invoke(null);

        $this->assertIsArray($paths);
        $this->assertContains('/usr/local/bin/pandoc', $paths);
        $this->assertContains('/usr/bin/pandoc', $paths);
        $this->assertContains('/opt/homebrew/bin/pandoc', $paths);
    }

    #[RequiresOperatingSystemFamily('Darwin')]
    public function testGetAdditionalPathsIncludesDarwinHomePaths(): void
    {
        $originalHome = getenv('HOME');
        putenv('HOME=/test/home');

        try {
            $reflection = new \ReflectionClass(PandocBinary::class);
            $method = $reflection->getMethod('getAdditionalPaths');

            $paths = $method->invoke(null);

            $this->assertContains('/test/home/.local/bin/pandoc', $paths);
            $this->assertContains('/test/home/.cabal/bin/pandoc', $paths);
        } finally {
            putenv($originalHome ? "HOME=$originalHome" : 'HOME');
        }
    }

    #[RequiresOperatingSystemFamily('Linux')]
    public function testGetAdditionalPathsReturnsUnixPaths(): void
    {
        $reflection = new \ReflectionClass(PandocBinary::class);
        $method = $reflection->getMethod('getAdditionalPaths');

        $paths = $method->invoke(null);

        $this->assertIsArray($paths);
        $this->assertContains('/usr/local/bin/pandoc', $paths);
        $this->assertContains('/usr/bin/pandoc', $paths);
        $this->assertContains('/opt/homebrew/bin/pandoc', $paths);
    }

    #[RequiresOperatingSystemFamily('Linux')]
    public function testGetAdditionalPathsIncludesHomePaths(): void
    {
        $originalHome = getenv('HOME');
        putenv('HOME=/test/home');

        try {
            $reflection = new \ReflectionClass(PandocBinary::class);
            $method = $reflection->getMethod('getAdditionalPaths');

            $paths = $method->invoke(null);

            $this->assertContains('/test/home/.local/bin/pandoc', $paths);
            $this->assertContains('/test/home/.cabal/bin/pandoc', $paths);
        } finally {
            putenv($originalHome ? "HOME=$originalHome" : 'HOME');
        }
    }

    #[RequiresOperatingSystemFamily('Linux')]
    public function testGetAdditionalPathsWithMissingHome(): void
    {
        $originalHome = getenv('HOME');
        putenv('HOME=');

        try {
            $reflection = new \ReflectionClass(PandocBinary::class);
            $method = $reflection->getMethod('getAdditionalPaths');

            $paths = $method->invoke(null);

            $this->assertIsArray($paths);
            $this->assertContains('/usr/local/bin/pandoc', $paths);
        } finally {
            putenv($originalHome ? "HOME=$originalHome" : 'HOME');
        }
    }

    public function testGetAdditionalPathsFiltersNonExistentPaths(): void
    {
        $reflection = new \ReflectionClass(PandocBinary::class);
        $method = $reflection->getMethod('getAdditionalPaths');

        $paths = $method->invoke(null);

        $this->assertIsArray($paths);
        foreach ($paths as $path) {
            $this->assertIsString($path);
            $this->assertNotEmpty($path);
        }
    }

    public function testIsValidPandocBinaryWithNonExecutableFile(): void
    {
        $reflection = new \ReflectionClass(PandocBinary::class);
        $method = $reflection->getMethod('isValidPandocBinary');

        $result = $method->invoke(null, '/fake/nonexistent/path');
        $this->assertFalse($result);
    }

    public function testIsValidPandocBinaryWithExecutableButInvalidOutput(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'fake_pandoc');
        chmod($tempFile, 0755);
        file_put_contents($tempFile, "#!/bin/bash\necho 'invalid tool output'");

        try {
            $reflection = new \ReflectionClass(PandocBinary::class);
            $method = $reflection->getMethod('isValidPandocBinary');

            $result = $method->invoke(null, $tempFile);
            $this->assertFalse($result);
        } finally {
            unlink($tempFile);
        }
    }

    public function testIsValidPandocBinaryWithProcessException(): void
    {
        $reflection = new \ReflectionClass(PandocBinary::class);
        $method = $reflection->getMethod('isValidPandocBinary');

        $result = $method->invoke(null, '/absolutely/nonexistent/path/pandoc');
        $this->assertFalse($result);
    }

    public function testRunListCommandWithGracefulFailure(): void
    {
        $binary = new PandocBinary('/fake/pandoc');
        $reflection = new \ReflectionClass($binary);
        $method = $reflection->getMethod('runListCommand');

        $result = $method->invoke($binary, '--list-highlight-languages', '', true);

        $this->assertSame([], $result);
    }

    public function testRunListCommandWithNonGracefulFailure(): void
    {
        $binary = new PandocBinary('/fake/pandoc');
        $reflection = new \ReflectionClass($binary);
        $method = $reflection->getMethod('runListCommand');

        $this->expectException(PandocException::class);
        $this->expectExceptionMessage('Test error');

        $method->invoke($binary, '--invalid-command', 'Test error', false);
    }

    public function testRunListCommandSuccessfulExecution(): void
    {
        $binary = new PandocBinary('/fake/pandoc');
        $reflection = new \ReflectionClass($binary);
        $method = $reflection->getMethod('runListCommand');

        $result = $method->invoke($binary, '--list-highlight-styles', '', true);
        $this->assertSame([], $result);
    }

    public function testToCommandArgsWithComplexOptions(): void
    {
        $options = Options::create([
            'flag' => true,
            'disabled' => false,
            'empty' => '',
            'null' => null,
            'number' => 42,
            'array' => ['ignored'],
        ]);

        $conversion = new Conversion(options: $options);

        $args = $conversion->toCommandArgs();

        $this->assertContains('--flag', $args);
        $this->assertContains('--number=42', $args);
        $this->assertNotContains('--disabled', $args);
        $this->assertNotContains('--empty', $args);
        $this->assertNotContains('--null', $args);
        $this->assertNotContains('--array', $args);
    }

    public function testConstructorWithLegacyConfig(): void
    {
        $conversion = new Conversion(config: [
            'input' => '/test.md',
            'outputFormat' => 'html',
            'nonExistent' => 'ignored',
        ]);

        $this->assertSame('/test.md', $conversion->inputPath);
        $this->assertSame('html', $conversion->outputFormat);
    }

    public function testConstructorWithInputPathConfig(): void
    {
        $conversion = new Conversion(config: [
            'inputPath' => '/explicit.md',
        ]);

        $this->assertSame('/explicit.md', $conversion->inputPath);
    }

    public function testValidateWithNonExistentOutputDir(): void
    {
        $conversion = new Conversion(
            inputContent: '# test',
            outputPath: '/nonexistent/dir/file.html'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Output directory not found: /nonexistent/dir');

        $conversion->validate();
    }

    public function testGetOutputPathWithLegacyOutput(): void
    {
        $conversion = new Conversion(output: '/legacy/output.html');
        $conversion->markExecuted(success: true, duration: 0.1);

        $result = $conversion->getOutputPath();

        $this->assertSame('/legacy/output.html', $result);
    }

    public function testWithMethodForNonExistentProperty(): void
    {
        $conversion = new Conversion();
        $result = $conversion->with('nonExistent', 'value');

        $this->assertInstanceOf(Conversion::class, $result);
        $this->assertNotSame($conversion, $result);
    }
}
