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

use Pandoc\Exception\UnsupportedConversionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnsupportedConversionException::class)]
final class UnsupportedConversionExceptionTest extends TestCase
{
    public function testConstructorWithFromAndToFormats(): void
    {
        $exception = new UnsupportedConversionException('custom', 'exotic');

        $this->assertStringContainsString("Conversion from 'custom' to 'exotic' is not supported", $exception->getMessage());
        $this->assertSame('custom', $exception->getFromFormat());
        $this->assertSame('exotic', $exception->getToFormat());
    }
}
