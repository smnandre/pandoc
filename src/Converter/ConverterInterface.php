<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Converter;

use Pandoc\Exception\ConversionException;
use Pandoc\Exception\PandocException;
use Pandoc\Options;
use Pandoc\PandocInfo;

/**
 * @author Simon André <smn.andre@gmail.com>
 */
interface ConverterInterface
{
    /**
     * @throws ConversionException
     */
    public function convert(Options $options): void;

    /**
     * @throws PandocException
     */
    public function getPandocInfo(): PandocInfo;

    /**
     * @return list<string>
     */
    public function listHighlightLanguages(): array;

    /**
     * @return list<string>
     */
    public function listHighlightStyles();

    /**
     * @return list<string>
     */
    public function listInputFormats(): array;

    /**
     * @return list<string>
     */
    public function listOutputFormats(): array;
}
