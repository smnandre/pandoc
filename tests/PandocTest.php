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

use Pandoc\Convertor\ProcessConvertor;
use Pandoc\Convertor\SystemConvertor;
use Pandoc\Options;
use Pandoc\Pandoc;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Pandoc::class)]
#[UsesClass(Options::class)]
#[UsesClass(ProcessConvertor::class)]
#[UsesClass(SystemConvertor::class)]
class PandocTest extends TestCase
{
    public function testCreate(): void
    {
        $pandoc = Pandoc::create();
        $this->assertInstanceOf(Pandoc::class, $pandoc);
    }
}
