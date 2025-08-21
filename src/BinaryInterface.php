<?php

declare(strict_types=1);

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
 * Minimal contract for a Pandoc binary adapter.
 */
interface BinaryInterface
{
    public function convert(Conversion $conversion): Conversion;

    public function supports(string $from, string $to): bool;

    public function getVersion(): string;

    /**
     * @return list<string>
     */
    public function getInputFormats(): array;

    /**
     * @return list<string>
     */
    public function getOutputFormats(): array;
}
