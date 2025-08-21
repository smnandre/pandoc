<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests\Unit\Exception;

use Pandoc\Exception\FileNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileNotFoundException::class)]
final class FileNotFoundExceptionTest extends TestCase
{
    public function testConstructorWithFilename(): void
    {
        $exception = new FileNotFoundException('missing-file.md');

        $this->assertStringContainsString('File not found: missing-file.md', $exception->getMessage());
        $this->assertSame('missing-file.md', $exception->getFilename());
    }

    public function testConstructorWithNonExistentFile(): void
    {
        $exception = new FileNotFoundException('non-existent-file.txt');

        $this->assertStringContainsString('File not found: non-existent-file.txt (file does not exist)', $exception->getMessage());
        $this->assertSame('non-existent-file.txt', $exception->getFilename());
    }

    public function testConstructorWithUnreadableFile(): void
    {
        $exception = new FileNotFoundException(__DIR__);

        $this->assertStringContainsString('File not found: '.__DIR__.' (path is a directory, not a file)', $exception->getMessage());
        $this->assertSame(__DIR__, $exception->getFilename());
    }

    public function testConstructorWithNonReadableFile(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        chmod($tempFile, 0200);

        try {
            $exception = new FileNotFoundException($tempFile);

            $this->assertStringContainsString('File not found: '.$tempFile.' (file is not readable - check permissions)', $exception->getMessage());
            $this->assertSame($tempFile, $exception->getFilename());
        } finally {
            unlink($tempFile);
        }
    }
}
