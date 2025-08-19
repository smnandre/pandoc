<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests\Test;

use Pandoc\Options;
use Pandoc\PandocInfo;
use Pandoc\Test\ConverterMock;
use Pandoc\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ConverterMock::class)]
#[UsesClass(PandocInfo::class)]
#[UsesClass(Options::class)]
class ConverterMockTest extends TestCase
{
    public function testConstruct(): void
    {
        $mock = new ConverterMock();
        $this->assertInstanceOf(ConverterMock::class, $mock);
    }

    public function testConstructWithInfo(): void
    {
        $mock = new ConverterMock(new PandocInfo('path', 'v', 'lua'));

        $mockInfo = $mock->getPandocInfo();

        $this->assertSame('path', $mockInfo->getPath());
        $this->assertSame('v', $mockInfo->getVersion());
        $this->assertSame('lua', $mockInfo->getLuaVersion());
    }

    public function testItUsesDefaultInfo(): void
    {
        $mock = new ConverterMock();

        $mockInfo = $mock->getPandocInfo();

        $this->assertSame('/pandoc', $mockInfo->getPath());
        $this->assertSame('0.0.0', $mockInfo->getVersion());
        $this->assertSame('0.0', $mockInfo->getLuaVersion());
    }

    #[Test]
    public function itCanConvertOptions(): void
    {
        $options = Options::create();

        $converter = new ConverterMock();
        $converter->convert($options);

        $this->assertSame($options, $converter->getLastOptions());
    }

    #[Test]
    public function itCanListHighlightLanguages(): void
    {
        $converter = new ConverterMock();
        $this->assertSame(['html', 'php', 'js'], $converter->listHighlightLanguages());
    }

    #[Test]
    public function itCanListHighlightStyles(): void
    {
        $converter = new ConverterMock();
        $this->assertSame(['breezedark', 'haddock', 'kate'], $converter->listHighlightStyles());
    }

    #[Test]
    public function itCanListInputFormats(): void
    {
        $converter = new ConverterMock();
        $this->assertSame(['markdown', 'rst'], $converter->listInputFormats());
    }

    #[Test]
    public function itCanListOutputFormats(): void
    {
        $converter = new ConverterMock();
        $this->assertSame(['html', 'docx', 'pdf'], $converter->listOutputFormats());
    }
}
