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
 * Thrown when unsupported conversion is attempted.
 */
final class UnsupportedConversionException extends PandocException
{
    private string $fromFormat;

    private string $toFormat;

    public function __construct(
        string $fromFormat,
        string $toFormat,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->fromFormat = $fromFormat;
        $this->toFormat = $toFormat;

        $message = "Conversion from '{$fromFormat}' to '{$toFormat}' is not supported by this pandoc installation";

        parent::__construct($message, $code, $previous);
    }

    public function getFromFormat(): string
    {
        return $this->fromFormat;
    }

    public function getToFormat(): string
    {
        return $this->toFormat;
    }
}
