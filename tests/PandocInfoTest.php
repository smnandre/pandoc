<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests;

use Pandoc\PandocInfo;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PandocInfo::class)]
class PandocInfoTest extends TestCase
{
    public function testConstruct(): void
    {
        $info = new PandocInfo('dev/null', '1.0.0');
        $this->assertInstanceOf(PandocInfo::class, $info);

        $info = new PandocInfo('dev/null', '1.0.0', '2.2');
        $this->assertInstanceOf(PandocInfo::class, $info);
    }

    public function testGetPath(): void
    {
        $info = new PandocInfo('dev/null', '1.0.0');
        $this->assertSame('dev/null', $info->getPath());
    }

    public function testGetVersion(): void
    {
        $info = new PandocInfo('dev/null', '1.0.0');
        $this->assertSame('1.0.0', $info->getVersion());
    }

    public function testGetLuaVersion(): void
    {
        $info = new PandocInfo('dev/null', '1.0.0');
        $this->assertNull($info->getLuaVersion());

        $info = new PandocInfo('dev/null', '1.0.0', '2.2');
        $this->assertSame('2.2', $info->getLuaVersion());
    }
}
