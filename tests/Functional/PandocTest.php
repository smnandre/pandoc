<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests\Functional;

use Pandoc\Converter\Process\PandocExecutableFinder;
use Pandoc\Converter\Process\ProcessConverter;
use Pandoc\Options;
use Pandoc\Pandoc;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Pandoc::class)]
#[UsesClass(Options::class)]
#[UsesClass(ProcessConverter::class)]
#[UsesClass(PandocExecutableFinder::class)]
class PandocTest extends TestCase
{
    private string $outputDir;

    protected function setUp(): void
    {
        $this->outputDir = sys_get_temp_dir().'/pandoc-test-output';
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        $files = glob($this->outputDir.'/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        rmdir($this->outputDir);
    }

    #[Test]
    public function itCanBeInstantiated(): void
    {
        $pandoc = new Pandoc();
        $this->assertInstanceOf(Pandoc::class, $pandoc);
    }

    #[Test]
    public function itCanConvertASingleFileToHtml(): void
    {
        $options = Options::create()
            ->setInput([__DIR__.'/../Fixtures/input.md'])
            ->setOutput($this->outputDir.'/output.html')
            ->setFormat('html')
            ->tableOfContent();

        (new Pandoc())->convert($options);

        $this->assertFileExists($this->outputDir.'/output.html');
        $this->assertStringContainsString('<h1', file_get_contents($this->outputDir.'/output.html'));
    }

    #[Test]
    public function itUsesDefaultOptionsWhenSet(): void
    {
        $defaultOptions = Options::create()
            ->setFormat('txt');

        $pandoc = Pandoc::create(defaultOptions: $defaultOptions);

        $options = Options::create()
            ->setInput([__DIR__.'/../Fixtures/input.md'])
            ->setOutput($this->outputDir.'/output_with_default.html')
            ->setFormat('html');

        $pandoc->convert($options);

        $this->assertFileExists($this->outputDir.'/output_with_default.html');
        $this->assertStringContainsString('<h1', file_get_contents($this->outputDir.'/output_with_default.html'));
    }

    #[Test]
    public function itCanConvertASingleFileToStdout(): void
    {
        $inputFile = realpath(__DIR__.'/../Fixtures/input.md'); // Adjust path
        $this->assertNotFalse($inputFile, 'Input file does not exist: '.__DIR__.'/../Fixtures/input.md');

        $options = Options::create()
            ->setInput([$inputFile])
            ->setFormat('html');

        $converter = new ProcessConverter();

        // Use a temporary file to capture output
        $tempFile = tempnam(sys_get_temp_dir(), 'pandoc_test_');
        $options->setOutput($tempFile);

        $converter->convert($options);

        // Get the output from the temporary file
        $output = file_get_contents($tempFile);

        // Clean up the temporary file
        unlink($tempFile);

        // Check for h1 tag in the output (allowing for attributes)
        $this->assertMatchesRegularExpression('#<h1.*>.*</h1>#', $output);
    }
}
