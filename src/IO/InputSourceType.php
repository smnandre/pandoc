<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\IO;

/**
 * Enumeration of input source types.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
enum InputSourceType
{
    case FILE;
    case FILES;
    case FINDER;
    case STRING;
    case STDIN;
}