<?php

namespace Pandoc\Tests\Result;

use DateTime;
use InvalidArgumentException;
use Pandoc\Result\DocumentMetadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Pandoc\Tests\TestCase;

#[CoversClass(DocumentMetadata::class)]
class DocumentMetadataTest extends TestCase
{
    #[Test]
    public function itConstructsAndAccessesFields(): void
    {
        $date = new DateTime('2021-01-01');
        $meta = new DocumentMetadata('T', 'A', $date, ['k1', 'k2'], ['x' => 5]);

        $this->assertSame('T', $meta->getTitle());
        $this->assertSame('A', $meta->getAuthor());
        $this->assertSame($date, $meta->getDate());
        $this->assertSame(['k1', 'k2'], $meta->getKeywords());
        $this->assertSame(['x' => 5], $meta->getCustomFields());
        $this->assertTrue($meta->hasField('x'));
        $this->assertNull($meta->getField('y'));

        $arr = $meta->toArray();
        $this->assertArrayHasKey('title', $arr);
        $this->assertArrayHasKey('author', $arr);
        $this->assertArrayHasKey('date', $arr);

        $meta2 = $meta->withTitle('New')->withAuthor(null)->withDate(null)
            ->withKeywords(['z'])->withCustomField('y', 'v');
        $this->assertSame('New', $meta2->getTitle());
        $this->assertNull($meta2->getAuthor());
        $this->assertNull($meta2->getDate());
        $this->assertSame(['z'], $meta2->getKeywords());
        $this->assertTrue($meta2->hasField('y'));
    }

    #[Test]
    public function itCreatesFromArrayAndValidates(): void
    {
        $data = ['title' => 'T', 'author' => 'A', 'date' => '2022-02-02',
                 'keywords' => 'a,b', 'extra' => 'v'];
        $meta = DocumentMetadata::fromArray($data);
        $this->assertSame(['a', 'b'], $meta->getKeywords());
        $this->assertTrue($meta->hasField('extra'));

        $this->expectException(InvalidArgumentException::class);
        DocumentMetadata::fromArray(['keywords' => 123]);
    }
}
