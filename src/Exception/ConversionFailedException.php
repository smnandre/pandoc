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
 * Thrown when conversion process fails.
 */
final class ConversionFailedException extends PandocException
{
    private ?string $pandocError = null;

    private ?string $inputFile = null;

    private ?string $outputFile = null;

    public function __construct(
        string $message = 'Conversion failed',
        ?string $pandocError = null,
        ?string $inputFile = null,
        ?string $outputFile = null,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->pandocError = $pandocError;
        $this->inputFile = $inputFile;
        $this->outputFile = $outputFile;

        $contextualMessage = $message;

        if ($inputFile) {
            $contextualMessage .= " (input: {$inputFile})";
        }

        if ($outputFile) {
            $contextualMessage .= " (output: {$outputFile})";
        }

        if ($pandocError) {
            $contextualMessage .= " - Pandoc error: {$pandocError}";
        }

        parent::__construct($contextualMessage, $code, $previous);
    }

    public function getPandocError(): ?string
    {
        return $this->pandocError;
    }

    public function getInputFile(): ?string
    {
        return $this->inputFile;
    }

    public function getOutputFile(): ?string
    {
        return $this->outputFile;
    }
}
