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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Pandoc\Tests\TestCase;

#[CoversClass(PandocExecutableFinder::class)]
class PandocExecutableFinderTest extends TestCase
{
    #[Test]
    public function it_can_find_pandoc_executable(): void
    {
        $finder = new PandocExecutableFinder();
        $path = $finder->find();

        $this->assertNotEmpty($path);
        $this->assertTrue(is_executable($path));
    }
}
