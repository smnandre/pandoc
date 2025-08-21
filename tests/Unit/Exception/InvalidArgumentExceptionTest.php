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

use Pandoc\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidArgumentException::class)]
final class InvalidArgumentExceptionTest extends TestCase
{
    public function testConstructorSetsMessage(): void
    {
        $exception = new InvalidArgumentException('Invalid input provided');

        $this->assertSame('Invalid input provided', $exception->getMessage());
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }
}
