<?php

declare(strict_types=1);

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests\Unit\Exception;

use Pandoc\Exception\InvalidFormatException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidFormatException::class)]
final class InvalidFormatExceptionTest extends TestCase
{
    public function testConstructorWithBasicMessage(): void
    {
        $exception = new InvalidFormatException('xyz', 'input');

        $this->assertSame('xyz', $exception->getFormat());
        $this->assertSame('input', $exception->getType());
        $this->assertSame('Invalid input format: xyz', $exception->getMessage());
    }

    public function testConstructorWithSimilarFormats(): void
    {
        $validFormats = ['markdown', 'html', 'docx'];
        $exception = new InvalidFormatException('markdwn', 'input', $validFormats);

        $this->assertStringContainsString('Did you mean: markdown', $exception->getMessage());
    }

    public function testConstructorWithManyValidFormatsNoSuggestions(): void
    {
        $validFormats = [
            'format01', 'format02', 'format03', 'format04', 'format05',
            'format06', 'format07', 'format08', 'format09', 'format10',
            'format11', 'format12', 'format13', 'format14', 'format15',
        ];

        $exception = new InvalidFormatException('xyz', 'output', $validFormats);
        $message = $exception->getMessage();

        $this->assertStringContainsString('Valid formats: format01, format02', $message);
        $this->assertStringContainsString('format10', $message);
        $this->assertStringContainsString('(and 5 more)', $message);
        $this->assertStringNotContainsString('format11', $message);
    }

    public function testConstructorWith10ValidFormatsExactly(): void
    {
        $validFormats = [
            'format01', 'format02', 'format03', 'format04', 'format05',
            'format06', 'format07', 'format08', 'format09', 'format10',
        ];

        $exception = new InvalidFormatException('xyz', 'input', $validFormats);
        $message = $exception->getMessage();

        $this->assertStringContainsString('Valid formats:', $message);
        $this->assertStringNotContainsString('more)', $message);
    }

    public function testToStringIncludesType(): void
    {
        $exception = new InvalidFormatException('xyz', 'output');
        $string = $exception->__toString();

        $this->assertStringContainsString('Type: output', $string);
    }

    public function testFindSimilarFormatsWithLevenshtein(): void
    {
        $validFormats = ['markdown', 'html', 'docx', 'latex'];
        $exception = new InvalidFormatException('markdwn', 'input', $validFormats);

        $this->assertStringContainsString('Did you mean: markdown', $exception->getMessage());
    }

    public function testFindSimilarFormatsWithSubstring(): void
    {
        $validFormats = ['commonmark', 'html', 'docx'];
        $exception = new InvalidFormatException('mark', 'input', $validFormats);

        $this->assertStringContainsString('Did you mean: commonmark', $exception->getMessage());
    }

    public function testConstructorWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');
        $exception = new InvalidFormatException('xyz', 'input', [], $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
