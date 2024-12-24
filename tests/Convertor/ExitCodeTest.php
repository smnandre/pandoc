<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests\Convertor;

use Pandoc\Convertor\ExitCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExitCode::class)]
class ExitCodeTest extends TestCase
{
    public function testSuccess(): void
    {
        $exitCode = ExitCode::Success;
        $this->assertSame(0, $exitCode->value);

        $exitCode = ExitCode::tryFrom(0);
        $this->assertTrue($exitCode->isSuccess());
    }

    public function testFailures(): void
    {
        foreach (ExitCode::cases() as $exitCode) {
            if ($exitCode->value === 0) {
                continue;
            }
            $this->assertNotSame(0, $exitCode->value);
            $this->assertTrue($exitCode->isError());
        }
    }
}
