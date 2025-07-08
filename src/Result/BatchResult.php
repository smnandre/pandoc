<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Result;

/**
 * Result of a batch conversion operation.
 *
 * @implements \IteratorAggregate<int, ConversionResult>
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class BatchResult implements \Countable, \IteratorAggregate
{
    /**
     * @param array<ConversionResult> $results
     * @param array<string> $errors
     */
    public function __construct(
        private readonly array $results = [],
        private readonly float $totalDuration = 0.0,
        private readonly array $errors = [],
    ) {}

    /**
     * Get all conversion results.
     *
     * @return array<ConversionResult>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Get total duration of all conversions.
     */
    public function getTotalDuration(): float
    {
        return $this->totalDuration;
    }

    /**
     * Get any errors that occurred during batch processing.
     *
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if all conversions were successful.
     */
    public function isSuccessful(): bool
    {
        return empty($this->errors) && !empty($this->results);
    }

    /**
     * Check if there were any errors.
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get number of successful conversions.
     */
    public function getSuccessfulCount(): int
    {
        return count(array_filter($this->results, fn(ConversionResult $result) => $result->isSuccessful()));
    }

    /**
     * Get number of failed conversions.
     */
    public function getFailedCount(): int
    {
        return count($this->errors);
    }

    /**
     * Get total number of conversions attempted.
     */
    public function getTotalCount(): int
    {
        return count($this->results) + count($this->errors);
    }

    /**
     * Get all output paths from all conversions.
     *
     * @return array<string>
     */
    public function getAllOutputPaths(): array
    {
        $paths = [];
        foreach ($this->results as $result) {
            $paths = array_merge($paths, $result->getOutputPaths());
        }
        return $paths;
    }

    /**
     * Get all warnings from all conversions.
     *
     * @return array<string>
     */
    public function getAllWarnings(): array
    {
        $warnings = [];
        foreach ($this->results as $result) {
            $warnings = array_merge($warnings, $result->getWarnings());
        }
        return $warnings;
    }

    /**
     * Check if any conversion had warnings.
     */
    public function hasWarnings(): bool
    {
        foreach ($this->results as $result) {
            if ($result->hasWarnings()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get success rate as a percentage.
     */
    public function getSuccessRate(): float
    {
        $total = $this->getTotalCount();
        if ($total === 0) {
            return 0.0;
        }

        return ($this->getSuccessfulCount() / $total) * 100;
    }

    /**
     * Count of results.
     */
    public function count(): int
    {
        return count($this->results);
    }

    /**
     * Iterator for results.
     *
     * @return \ArrayIterator<int, ConversionResult>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->results);
    }

    /**
     * Get summary of batch operation.
     */
    public function getSummary(): string
    {
        $total = $this->getTotalCount();
        $successful = $this->getSuccessfulCount();
        $failed = $this->getFailedCount();

        $parts = [];

        if ($total > 0) {
            $parts[] = "Processed {$total} document" . ($total > 1 ? 's' : '');

            if ($successful > 0) {
                $parts[] = "{$successful} successful";
            }

            if ($failed > 0) {
                $parts[] = "{$failed} failed";
            }
        }

        if ($this->totalDuration > 0) {
            $parts[] = sprintf("in %.3fs", $this->totalDuration);
        }

        if ($this->hasWarnings()) {
            $warningCount = count($this->getAllWarnings());
            $parts[] = "with {$warningCount} warning" . ($warningCount > 1 ? 's' : '');
        }

        return implode(' ', $parts) ?: 'Batch operation completed';
    }

    /**
     * Create a successful batch result.
     *
     * @param array<ConversionResult> $results
     */
    public static function success(array $results, float $totalDuration = 0.0): self
    {
        return new self($results, $totalDuration, []);
    }

    /**
     * Create a batch result with some failures.
     *
     * @param array<ConversionResult> $results
     * @param array<string> $errors
     */
    public static function withFailures(array $results, array $errors, float $totalDuration = 0.0): self
    {
        return new self($results, $totalDuration, $errors);
    }
}
