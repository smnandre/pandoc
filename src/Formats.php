<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc;

final class Formats
{
    private const array EXTENSIONS = [
        'gfm' => 'md',
        'markdown' => 'md',
        'markdown_github' => 'md',
        'markdown_mmd' => 'md',
        'markdown_phpextra' => 'md',
        'markdown_strict' => 'md',
    ];

    public static function getExtension(string $format): ?string
    {
        return self::EXTENSIONS[$format] ?? null;
    }
}
