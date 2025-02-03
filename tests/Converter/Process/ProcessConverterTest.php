<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests\Converter\Process;

use Pandoc\Converter\Process\PandocExecutableFinder;
use Pandoc\Converter\Process\ProcessConverter;
use Pandoc\Exception\ConversionException;
use Pandoc\Options;
use Pandoc\PandocInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Pandoc\Tests\TestCase;
use Symfony\Component\Process\ExecutableFinder;

#[CoversClass(ProcessConverter::class)]
#[UsesClass(Options::class)]
#[UsesClass(PandocInfo::class)]
#[UsesClass(PandocExecutableFinder::class)]
class ProcessConverterTest extends TestCase
{
    private MockObject|LoggerInterface $loggerMock;
    private ProcessConverter $converter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->converter = new ProcessConverter(
            logger: $this->loggerMock,
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanupTemporaryDirectory();
    }

    #[Test]
    public function it_throws_exception_if_pandoc_executable_is_not_found(): void
    {
        if (null !== (new ExecutableFinder())->find('pandoc')) {
            $this->markTestSkipped('Pandoc executable found on the system.');
        }

        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Pandoc executable not found.');

        new ProcessConverter(executable: '/dev/null');
    }

    #[Test]
    public function it_converts_single_file_with_options(): void
    {
        $inputFile = $this->getFixturesDirectory() . '/input.md';
        $outputFile = $this->getTemporaryDirectory() . '/output.html';

        $options = Options::create()
            ->setInput([$inputFile])
            ->setOutput($outputFile)
            ->setFormat('html')
            ->tableOfContent();

        $this->converter->convert($options);

        $this->assertFileExists($outputFile);
        $this->assertStringContainsString('This is a test input file', file_get_contents($outputFile));
    }

    #[Test]
    public function it_converts_markdown_to_rst(): void
    {
        $inputFile = $this->createTemporaryFile('# Test Heading');
        $outputFile = $this->getTemporaryDirectory() . '/output.rst';

        $options = Options::create()
            ->setInput([$inputFile])
            ->setOutput($outputFile)
            ->setFormat('rst')
            ->from('markdown');

        $this->converter->convert($options);

        $this->assertFileExists($outputFile);
        $this->assertStringContainsString('Test Heading', file_get_contents($outputFile));
    }

    #[Test]
    public function it_converts_rst_to_markdown(): void
    {
        $inputFile = $this->createTemporaryFile("Test Heading\n============");
        $outputFile = $this->getTemporaryDirectory() . '/output.md';

        $options = Options::create()
            ->setInput([$inputFile])
            ->setOutput($outputFile)
            ->setFormat('markdown')
            ->from('rst');

        $this->converter->convert($options);

        $this->assertFileExists($outputFile);
        $this->assertStringContainsString('# Test Heading', file_get_contents($outputFile));
    }

    #[Test]
    public function it_converts_multiple_files_using_finder(): void
    {
        $outputDir = $this->getTemporaryDirectory();

        $finder = Finder::create()
            ->files()
            ->in($this->getFixturesDirectory())
            ->name('chapter*.md');

        $options = Options::create()
            ->setInput($finder)
            ->setOutputDir($outputDir)
            ->setFormat('docx');

        $this->converter->convert($options);

        $this->assertFileExists($outputDir . '/chapter1.docx');
        $this->assertFileExists($outputDir . '/chapter2.docx');
        $this->assertFileExists($outputDir . '/chapter3.docx');
    }

    #[Test]
    public function it_throws_exception_on_conversion_failure(): void
    {
        $this->expectException(ConversionException::class);
        $inputFile = $this->getFixturesDirectory() . '/input.md';
        $outputFile = $this->getTemporaryDirectory() . '/output.html';

        $options = Options::create()
            ->setInput([$inputFile])
            ->setOutput($outputFile)
            ->setFormat('invalid-format');

        $this->converter->convert($options);
    }

    #[Test]
    public function it_throws_exception_if_output_dir_not_specified_with_finder(): void
    {
        $this->expectException(ConversionException::class);
        $this->expectExceptionMessage('Output directory must be specified when converting multiple files.');

        $finder = Finder::create()
            ->files()
            ->in($this->getFixturesDirectory())
            ->name('*.md');

        $options = Options::create()
            ->setInput($finder)
            ->setFormat('html');

        $this->converter->convert($options);
    }

    #[Test]
    public function it_can_convert_a_single_file_to_stdout(): void
    {
        $inputFile = realpath(__DIR__ . '/../../Fixtures/input.md');
        $this->assertNotFalse($inputFile, 'Input file does not exist: ' . __DIR__ . '/../../Fixtures/input.md');

        $options = Options::create()
            ->setInput([$inputFile])
            ->setFormat('html');

        $converter = new ProcessConverter();

        $tempFile = tempnam(sys_get_temp_dir(), 'pandoc_test_');
        $options->setOutput($tempFile);
        $converter->convert($options);
        $output = file_get_contents($tempFile);
        unlink($tempFile);

        $this->assertMatchesRegularExpression('#<h1.*>.*</h1>#', $output);
    }

    #[Test]
    public function it_returns_pandoc_info(): void
    {
        $info = $this->converter->getPandocInfo();

        $this->assertInstanceOf(PandocInfo::class, $info);
        $this->assertNotEmpty($info->getVersion());
    }

    #[Test]
    public function it_can_list_input_formats(): void
    {
        $inputFormats = $this->converter->listInputFormats();
        $this->assertContains('markdown', $inputFormats);
        $this->assertContains('rst', $inputFormats);
    }

    #[Test]
    public function it_can_list_output_formats(): void
    {
        $outputFormats = $this->converter->listOutputFormats();
        $this->assertContains('html', $outputFormats);
        $this->assertContains('pdf', $outputFormats);
    }

    #[Test]
    public function it_can_list_highlight_languages(): void
    {
        $languages = $this->converter->listHighlightLanguages();
        $this->assertContains('html', $languages);
        $this->assertContains('php', $languages);
        $this->assertContains('markdown', $languages);
    }

    #[Test]
    public function it_can_list_highlight_styles(): void
    {
        $styles = $this->converter->listHighlightStyles();
        $this->assertContains('breezedark', $styles);
        $this->assertContains('haddock', $styles);
        $this->assertContains('kate', $styles);
    }
}
