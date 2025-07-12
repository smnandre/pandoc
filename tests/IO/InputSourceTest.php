<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests\IO;

use InvalidArgumentException;
use Pandoc\Format\InputFormat;
use Pandoc\IO\InputSource;
use Pandoc\IO\InputSourceType;
use Pandoc\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

#[CoversClass(InputSource::class)]
class InputSourceTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->cleanupTemporaryDirectory();
        parent::tearDown();
    }

    #[Test]
    public function fileThrowsExceptionIfNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        InputSource::file('/non/existing/file.md');
    }

    #[Test]
    public function itCreatesFromFileAndDetectsFormat(): void
    {
        $file = $this->createTemporaryFile('content');
        $path = $file . '.md';
        rename($file, $path);
        file_put_contents($path, 'data');
        $source = InputSource::file($path);

        $this->assertSame(InputSourceType::FILE, $source->getType());
        $this->assertSame(InputFormat::MARKDOWN, $source->getFormatOrDetect());
        $this->assertSame(['data'], [substr($source->getContent(), 0, 4)]);
        $this->assertSame([$path], $source->getFilePaths());
        $this->assertFalse($source->isMultiple());
        $this->assertSame(1, $source->count());
    }

    #[Test]
    public function itCreatesFromMultipleFiles(): void
    {
        $file1 = $this->createTemporaryFile('a');
        $file2 = $this->createTemporaryFile('b');
        $source = InputSource::files([$file1, $file2], InputFormat::MARKDOWN);

        $this->assertSame(InputSourceType::FILES, $source->getType());
        $this->assertSame(InputFormat::MARKDOWN, $source->getFormat());
        $this->assertTrue($source->isMultiple());
        $this->assertCount(2, $source->getFilePaths());
        $this->assertSame(2, $source->count());
    }

    #[Test]
    public function itCreatesFromStringAndHandlesContent(): void
    {
        $source = InputSource::string('hello');

        $this->assertSame(InputSourceType::STRING, $source->getType());
        $this->assertNull($source->getFormat());
        $this->assertSame('hello', $source->getContent());
        $this->assertSame([], $source->getFilePaths());
        $this->assertFalse($source->isMultiple());
        $this->assertSame(1, $source->count());
    }

    #[Test]
    public function itAllowsOverridingFormat(): void
    {
        $src = InputSource::string('x');
        $new = $src->withFormat(InputFormat::HTML);
        $this->assertSame(InputFormat::HTML, $new->getFormat());
    }
}
