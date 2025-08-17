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
 * Thrown when input file is not found or not readable.
 */
final class FileNotFoundException extends PandocException
{
    private string $filename;

    public function __construct(string $filename, int $code = 0, ?\Throwable $previous = null)
    {
        $this->filename = $filename;

        $message = "File not found: {$filename}";

        if (!file_exists($filename)) {
            $message .= ' (file does not exist)';
        } elseif (!is_readable($filename)) {
            $message .= ' (file is not readable - check permissions)';
        } elseif (is_dir($filename)) {
            $message .= ' (path is a directory, not a file)';
        }

        parent::__construct($message, $code, $previous);
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
