<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Converter\Process;

use Symfony\Component\Process\ExecutableFinder;

/**
 * Finds the Pandoc executable.
 *
 * @internal
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class PandocExecutableFinder
{
    public function find(): ?string
    {
        return (new ExecutableFinder())->find('pandoc');
    }
}
