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

/**
 * @internal
 */
final class Extensions
{
    /**
     * @var array<string, bool>
     */
    private array $extensions = [];

    /**
     * @param array<string, bool> $extensions
     */
    public function __construct(array $extensions = [])
    {
        foreach ($extensions as $extension => $enabled) {
            $this->extensions[$extension] = $enabled;
        }
    }

    public function has(string $extension): bool
    {
        return isset($this->extensions[$extension]);
    }

    public function enable(string $extension): void
    {
        if (!$this->has($extension)) {
            throw new \InvalidArgumentException(sprintf('Extension "%s" is not supported.', $extension));
        }

        $this->extensions[$extension] = true;
    }

    public function disable(string $extension): void
    {
        if (!$this->has($extension)) {
            throw new \InvalidArgumentException(sprintf('Extension "%s" is not supported.', $extension));
        }

        $this->extensions[$extension] = false;
    }

    public function isEnabled(string $extension): bool
    {
        if (!$this->has($extension)) {
            throw new \InvalidArgumentException(sprintf('Extension "%s" is not supported.', $extension));
        }

        return $this->extensions[$extension];
    }
}
