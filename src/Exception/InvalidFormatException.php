<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Exception;

/**
 * Thrown when invalid format is specified.
 */
final class InvalidFormatException extends PandocException
{
    private string $format;

    private string $type; // 'input' or 'output'

    /**
     * @param list<string> $validFormats
     */
    public function __construct(
        string $format,
        string $type = 'format',
        array $validFormats = [],
        ?\Throwable $previous = null,
    ) {
        $this->format = $format;
        $this->type = $type;

        $message = "Invalid {$type} format: {$format}";

        if ($validFormats) {
            $suggestions = $this->findSimilarFormats($format, $validFormats);

            if (!empty($suggestions)) {
                $message .= '. Did you mean: '.implode(', ', $suggestions).'?';
            } else {
                $message .= '. Valid formats: '.implode(', ', \array_slice($validFormats, 0, 10));

                if (\count($validFormats) > 10) {
                    $message .= ' (and '.(\count($validFormats) - 10).' more)';
                }
            }
        }

        parent::__construct($message, 0, $previous);
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function __toString(): string
    {
        return parent::__toString()."\nType: {$this->type}";
    }

    /**
     * @param list<string> $validFormats
     *
     * @return list<string>
     */
    private function findSimilarFormats(string $format, array $validFormats): array
    {
        $suggestions = [];
        $format = strtolower($format);

        foreach ($validFormats as $validFormat) {
            $validFormatLower = strtolower($validFormat);

            if (str_contains($validFormatLower, $format) || str_contains($format, $validFormatLower)) {
                $suggestions[] = $validFormat;
                continue;
            }

            if (levenshtein($format, $validFormatLower) <= 2) {
                $suggestions[] = $validFormat;
            }
        }

        return \array_slice($suggestions, 0, 3);
    }
}
