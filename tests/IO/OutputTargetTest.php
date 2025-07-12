<?php

namespace Pandoc\Tests\IO;

use LogicException;
use Pandoc\Format\OutputFormat;
use Pandoc\IO\OutputTarget;
use Pandoc\IO\OutputTargetType;
use Pandoc\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(OutputTarget::class)]
class OutputTargetTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->cleanupTemporaryDirectory();
        parent::tearDown();
    }

    #[Test]
    public function itCreatesFileTarget(): void
    {
        $dir = $this->createTemporaryDirectory();
        $path = $dir . '/out.html';
        $target = OutputTarget::file($path);

        $this->assertSame(OutputTargetType::FILE, $target->getType());
        $this->assertSame($path, $target->getTarget());
        $this->assertSame(dirname($path), $target->getDirectory());
        $this->assertFalse($target->supportsMultipleFiles());
        $this->assertFalse($target->returnsString());
        $this->assertFalse($target->isStdout());
        $this->assertFalse($target->isTemporary());
        $this->assertSame($path, $target->generateOutputPath('input.md', OutputFormat::HTML));
    }

    #[Test]
    public function itCreatesDirectoryTarget(): void
    {
        $dir = $this->createTemporaryDirectory();
        $target = OutputTarget::directory($dir);

        $this->assertSame(OutputTargetType::DIRECTORY, $target->getType());
        $this->assertSame($dir, $target->getTarget());
        $this->assertSame($dir, $target->getDirectory());
        $this->assertTrue($target->supportsMultipleFiles());
        $out = $target->generateOutputPath('file.md', OutputFormat::HTML);
        $this->assertStringEndsWith('file.html', $out);
    }

    #[Test]
    public function itCreatesStringTarget(): void
    {
        $target = OutputTarget::string();

        $this->assertSame(OutputTargetType::STRING, $target->getType());
        $this->assertTrue($target->returnsString());
        $this->assertFalse($target->supportsMultipleFiles());
        $this->assertNotNull($target->getTarget());
    }

    #[Test]
    public function itCreatesStdoutTarget(): void
    {
        $target = OutputTarget::stdout();
        $this->assertSame(OutputTargetType::STDOUT, $target->getType());
        $this->assertTrue($target->isStdout());
        $this->assertNull($target->getDirectory());
    }

    #[Test]
    public function itCreatesTemporaryTarget(): void
    {
        $target = OutputTarget::temporary('.tmp', $this->createTemporaryDirectory());
        $this->assertSame(OutputTargetType::FILE, $target->getType());
        $this->assertSame(dirname($target->getTarget()), $target->getDirectory());
        // cleanup will remove the temp file
        $target->cleanup();
        $this->assertFileDoesNotExist($target->getTarget());
    }
}
