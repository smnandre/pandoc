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

use Pandoc\Exception\ConversionFailedException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConversionFailedException::class)]
final class ConversionFailedExceptionTest extends TestCase
{
    public function testConstructorWithContextInformation(): void
    {
        $exception = new ConversionFailedException(
            message: 'Conversion failed',
            pandocError: 'Unknown format: xyz',
            inputFile: 'input.md',
            outputFile: 'output.xyz'
        );

        $this->assertStringContainsString('Conversion failed', $exception->getMessage());
        $this->assertStringContainsString('input: input.md', $exception->getMessage());
        $this->assertStringContainsString('output: output.xyz', $exception->getMessage());
        $this->assertStringContainsString('Unknown format: xyz', $exception->getMessage());

        $this->assertSame('Unknown format: xyz', $exception->getPandocError());
        $this->assertSame('input.md', $exception->getInputFile());
        $this->assertSame('output.xyz', $exception->getOutputFile());
    }
}
