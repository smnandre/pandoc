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

use Pandoc\Converter\ConverterInterface;
use Pandoc\Converter\Process\PandocExecutableFinder;
use Pandoc\Converter\Process\ProcessConverter;
use Pandoc\Options;
use Pandoc\Pandoc;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use Pandoc\Tests\TestCase;

#[CoversClass(Pandoc::class)]
#[UsesClass(Options::class)]
#[UsesClass(ProcessConverter::class)]
#[UsesClass(PandocExecutableFinder::class)]
class PandocTest extends TestCase
{
    private MockObject|ConverterInterface $converterMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->converterMock = $this->createMock(ConverterInterface::class);
    }

    #[Test]
    public function it_can_be_instantiated(): void
    {
        $pandoc = new Pandoc();
        $this->assertInstanceOf(Pandoc::class, $pandoc);
    }

    #[Test]
    public function it_delegates_conversion_to_converter(): void
    {
        $options = Options::create();

        $this->converterMock->expects($this->once())
            ->method('convert')
            ->with($options);

        $pandoc = new Pandoc($this->converterMock);
        $pandoc->convert($options);
    }

    #[Test]
    public function it_uses_default_options_if_provided(): void
    {
        $defaultOptions = Options::create()->setFormat('html');
        $options = Options::create()->setInput(['input.md'])->setOutput('output.html');

        // The converter should receive the merged options
        $mergedOptions = clone $defaultOptions;
        $mergedOptions->setInput(['input.md'])->setOutput('output.html');

        $this->converterMock->expects($this->once())
            ->method('convert')
            ->with($this->callback(function (Options $passedOptions) use ($mergedOptions) {
                // Compare the options as strings for easier debugging
                return (string) $passedOptions === (string) $mergedOptions;
            }));

        $pandoc = new Pandoc($this->converterMock, $defaultOptions);
        $pandoc->convert($options);
    }

    #[Test]
    public function it_can_be_created_with_default_options(): void
    {
        $defaultOptions = Options::create()->setFormat('html');
        $pandoc = Pandoc::create(defaultOptions: $defaultOptions);

        $this->assertInstanceOf(Pandoc::class, $pandoc);
    }
}
