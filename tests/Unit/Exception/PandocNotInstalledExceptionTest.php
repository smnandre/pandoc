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

use Pandoc\Exception\PandocNotInstalledException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PandocNotInstalledException::class)]
final class PandocNotInstalledExceptionTest extends TestCase
{
    public function testDefaultMessageIncludesInstallationLink(): void
    {
        $exception = new PandocNotInstalledException();

        $this->assertStringContainsString('Pandoc not found', $exception->getMessage());
        $this->assertStringContainsString('https://pandoc.org/installing.html', $exception->getMessage());
    }
}
