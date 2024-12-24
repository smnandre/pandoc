<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests;

use Pandoc\Convertor\ConvertorInterface;

final class MockConvertor // implements ConvertorInterface
{
    public function __construct(
        private string $result,
    ) {}

    public function convert(string $input, string $output): string
    {
        return $this->result;
    }
}
