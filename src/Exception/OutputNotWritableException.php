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
 * Thrown when output directory is not writable.
 */
final class OutputNotWritableException extends PandocException
{
    private string $outputPath;

    public function __construct(string $outputPath, int $code = 0, ?\Throwable $previous = null)
    {
        $this->outputPath = $outputPath;

        $message = "Output path is not writable: {$outputPath}";

        if (!file_exists(\dirname($outputPath))) {
            $message .= ' (directory does not exist)';
        } elseif (!is_writable(\dirname($outputPath))) {
            $message .= ' (directory is not writable - check permissions)';
        }

        parent::__construct($message, $code, $previous);
    }

    public function getOutputPath(): string
    {
        return $this->outputPath;
    }
}
