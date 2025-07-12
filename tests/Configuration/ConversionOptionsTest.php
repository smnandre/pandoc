<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests\Configuration;

use InvalidArgumentException;
use Pandoc\Configuration\ConversionOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Pandoc\Tests\TestCase;

#[CoversClass(ConversionOptions::class)]
class ConversionOptionsTest extends TestCase
{
    #[Test]
    public function itAddsVariablesAndMetadataFiles(): void
    {
        $opts = ConversionOptions::create()
            ->variable('foo', 'bar')
            ->metadataFile('meta.yaml');

        $this->assertSame(['foo' => 'bar'], $opts->getVariables());
        $this->assertSame(['meta.yaml'], $opts->getMetadataFiles());
        $str = (string) $opts;
        $this->assertStringContainsString('--variable=' . escapeshellarg('foo:bar'), $str);
        $this->assertStringContainsString('--metadata-file=' . escapeshellarg('meta.yaml'), $str);
    }

    #[Test]
    public function itMergesVariablesAndMetadataFiles(): void
    {
        $o1 = ConversionOptions::create()->variable('a', '1')->metadataFile('a.yml');
        $o2 = ConversionOptions::create()->variable('b', '2')->metadataFile('b.yml');
        $merged = $o1->merge($o2);

        $this->assertSame(['a' => '1', 'b' => '2'], $merged->getVariables());
        $this->assertSame(['a.yml', 'b.yml'], $merged->getMetadataFiles());
    }

    #[Test]
    public function includeThrowsOnInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ConversionOptions::create()->include('unknown', 'file.txt');
    }
}
