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
 * Thrown when pandoc binary is not found or not executable.
 */
final class PandocNotInstalledException extends PandocException
{
    public function __construct(string $message = 'Pandoc not installed', int $code = 0, ?\Throwable $previous = null)
    {
        if ('Pandoc not installed' === $message) {
            $message = 'Pandoc not found. Please install from https://pandoc.org/installing.html';
        }

        parent::__construct($message, $code, $previous);
    }
}
