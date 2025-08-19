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

use Pandoc\Converter\Process\ExitCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExitCode::class)]
class ExitCodeTest extends TestCase
{
    #[Test]
    public function testSuccess(): void
    {
        $exitCode = ExitCode::tryFrom(0);
        $this->assertTrue($exitCode->isSuccess());
    }

    #[Test]
    public function testFailures(): void
    {
        foreach (ExitCode::cases() as $exitCode) {
            if (0 === $exitCode->value) {
                continue;
            }
            $this->assertNotSame(0, $exitCode->value);
            $this->assertTrue($exitCode->isError());
        }
    }
}
