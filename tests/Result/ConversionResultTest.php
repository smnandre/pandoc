<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests\Result;

use DateTime;
use Pandoc\Result\ConversionResult;
use Pandoc\Result\DocumentMetadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Pandoc\Tests\TestCase;

#[CoversClass(ConversionResult::class)]
class ConversionResultTest extends TestCase
{
    #[Test]
    public function itHandlesFileResultAndWarnings(): void
    {
        $res = ConversionResult::fileResult(['one.txt', 'two.txt'], null, 1.23, ['w1']);
        $this->assertSame(['one.txt', 'two.txt'], $res->getOutputPaths());
        $this->assertSame('one.txt', $res->getOutputPath());
        $this->assertNull($res->getContent());
        $this->assertSame(1.23, $res->getDuration());
        $this->assertSame(['w1'], $res->getWarnings());
        $this->assertTrue($res->isSuccessful());
        $this->assertTrue($res->hasWarnings());
        $this->assertFalse($res->isStringResult());
        $this->assertTrue($res->isFileResult());
        $this->assertSame(2, $res->getOutputCount());

        $summary = $res->getSummary();
        $this->assertStringContainsString('Generated 2 files', $summary);
        $this->assertStringContainsString('in 1.230s', $summary);
        $this->assertStringContainsString('with 1 warning', $summary);
    }

    #[Test]
    public function itHandlesStringResultWithMetadata(): void
    {
        $meta = new DocumentMetadata('T', 'A', new DateTime('2020-01-01'), ['k'], ['x' => 1]);
        $res = ConversionResult::stringResult('<p/>', $meta, 0.5, []);

        $this->assertSame([], $res->getOutputPaths());
        $this->assertNull($res->getOutputPath());
        $this->assertSame('<p/>', $res->getContent());
        $this->assertSame($meta, $res->getMetadata());
        $this->assertTrue($res->isStringResult());
        $this->assertFalse($res->isFileResult());

        $res2 = $res->withWarnings(['w2']);
        $this->assertSame(['w2'], $res2->getWarnings());

        $summary = $res2->getSummary();
        $this->assertStringContainsString('Generated 4 characters', $summary);
        $this->assertStringContainsString('with 1 warning', $summary);
    }
}
