<?php

namespace Pandoc\Tests\Result;

use Pandoc\Result\BatchResult;
use Pandoc\Result\ConversionResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Pandoc\Tests\TestCase;

#[CoversClass(BatchResult::class)]
class BatchResultTest extends TestCase
{
    #[Test]
    public function itHandlesSuccessAndFailures(): void
    {
        $ok1 = ConversionResult::fileResult('a');
        $ok2 = ConversionResult::fileResult('b', null, 0.0, ['warn']);
        $batch = BatchResult::withFailures([$ok1, $ok2], ['err'], 2.5);

        $this->assertSame([$ok1, $ok2], $batch->getResults());
        $this->assertSame(['err'], $batch->getErrors());
        $this->assertSame(2.5, $batch->getTotalDuration());
        $this->assertFalse($batch->isSuccessful());
        $this->assertTrue($batch->hasErrors());
        $this->assertSame(2, $batch->getSuccessfulCount());
        $this->assertSame(1, $batch->getFailedCount());
        $this->assertSame(3, $batch->getTotalCount());
        $this->assertSame(['a', 'b'], $batch->getAllOutputPaths());
        $this->assertSame(['warn'], $batch->getAllWarnings());
        $this->assertTrue($batch->hasWarnings());
        $this->assertGreaterThan(0, $batch->getSuccessRate());
        $this->assertCount(2, $batch);

        $summary = $batch->getSummary();
        $this->assertStringContainsString('Processed 3 documents', $summary);
        $this->assertStringContainsString('2 successful', $summary);
        $this->assertStringContainsString('1 failed', $summary);
        $this->assertStringContainsString('in', $summary);
        $this->assertStringContainsString('with 1 warning', $summary);
    }

    #[Test]
    public function itHandlesEmptyBatch(): void
    {
        $batch = new BatchResult([], 0.0, []);
        $this->assertSame('Batch operation completed', $batch->getSummary());
        $this->assertSame(0.0, $batch->getSuccessRate());
    }
}
