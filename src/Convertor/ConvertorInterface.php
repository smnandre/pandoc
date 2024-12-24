<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Convertor;

use Pandoc\Options;

interface ConvertorInterface
{
    /**
     * Returns the list of supported input formats.
     *
     * @return list<string>
     */
    public function listInputFormats(): array;

    /**
     * Returns the list of supported output formats.
     *
     * @return list<string>
     */
    public function listOutputFormats(): array;

    /**
     * @param string $input   The input to convert from
     * @param string $output  The output to convert to
     *
     * @return string
     */
    public function convert(string $input, string $output, ?Options $options = null): string;
}
