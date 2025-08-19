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
use Pandoc\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(PandocExecutableFinder::class)]
class PandocExecutableFinderTest extends TestCase
{
    #[Test]
    public function itCanFindPandocExecutable(): void
    {
        $finder = new PandocExecutableFinder();
        $path = $finder->find();

        $this->assertNotNull($path);
        $this->assertIsString($path);
        $this->assertFileExists($path);
        $this->assertTrue(is_executable($path));
    }
}
