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
use Psr\Log\LoggerInterface;

final class SystemConvertor extends ProcessConvertor
{
    public static function create(?Options $options = null, ?LoggerInterface $logger = null): self
    {
        $executable = 'pandoc';

        return new self($executable, $options, $logger);
    }
}
