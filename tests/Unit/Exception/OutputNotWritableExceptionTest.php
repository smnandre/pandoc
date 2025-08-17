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

use Pandoc\Exception\OutputNotWritableException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OutputNotWritableException::class)]
final class OutputNotWritableExceptionTest extends TestCase
{
    public function testConstructorWithOutputPath(): void
    {
        $exception = new OutputNotWritableException('/readonly/output.pdf');

        $this->assertStringContainsString('Output path is not writable: /readonly/output.pdf', $exception->getMessage());
        $this->assertSame('/readonly/output.pdf', $exception->getOutputPath());
    }

    public function testConstructorWithNonExistentDirectory(): void
    {
        $exception = new OutputNotWritableException('/nonexistent/output.pdf');

        $this->assertStringContainsString('Output path is not writable: /nonexistent/output.pdf (directory does not exist)', $exception->getMessage());
        $this->assertSame('/nonexistent/output.pdf', $exception->getOutputPath());
    }

    public function testConstructorWithNonWritableDirectory(): void
    {
        $tempDir = sys_get_temp_dir().'/test-non-writable-dir';
        mkdir($tempDir, 0500);

        try {
            $exception = new OutputNotWritableException($tempDir.'/output.pdf');

            $this->assertStringContainsString('Output path is not writable: '.$tempDir.'/output.pdf (directory is not writable - check permissions)', $exception->getMessage());
            $this->assertSame($tempDir.'/output.pdf', $exception->getOutputPath());
        } finally {
            rmdir($tempDir);
        }
    }
}
