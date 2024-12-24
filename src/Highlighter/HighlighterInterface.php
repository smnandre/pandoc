<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Highlighter;

interface HighlighterInterface
{
    /**
     * @return list<string>
     */
    public function listHighlightLanguages(): array;

    /**
     * @return list<string>
     */
    public function listHighlightStyles(): array;

    // public function highlight(string $input, string $output, string $language, string $style): string;
}
