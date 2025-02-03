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

final readonly class PandocInfo
{
    public function __construct(
        private string $path,
        private string $version,
        private string|null $luaVersion = null,
    ) {}

    public function getPath(): string
    {
        return $this->path;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getLuaVersion(): string|null
    {
        return $this->luaVersion;
    }
}
