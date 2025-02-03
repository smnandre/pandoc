<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Test;

use Pandoc\Converter\ConverterInterface;
use Pandoc\Options;
use Pandoc\PandocInfo;

/**
 * @author Simon André <smn.andre@gmail.com>
 */
class ConverterMock implements ConverterInterface
{
    private ?Options $lastOptions = null;
    private ?PandocInfo $pandocInfo;

    public function __construct(?PandocInfo $pandocInfo = null)
    {
        $this->pandocInfo = $pandocInfo;
    }

    public function convert(Options $options): void
    {
        $this->lastOptions = $options;
    }

    public function getLastOptions(): ?Options
    {
        return $this->lastOptions;
    }

    public function getPandocInfo(): PandocInfo
    {
        return $this->pandocInfo ??= new PandocInfo(
            '/pandoc',
            '0.0.0',
            '0.0',
        );
    }

    /**
     * @return list<string>
     */
    public function listHighlightLanguages(): array
    {
        return ['html', 'php', 'js'];
    }

    /**
     * @return list<string>
     */
    public function listHighlightStyles(): array
    {
        return ['breezedark', 'haddock', 'kate'];
    }

    /**
     * @return list<string>
     */
    public function listInputFormats(): array
    {
        return ['markdown', 'rst'];
    }

    /**
     * @return list<string>
     */
    public function listOutputFormats(): array
    {
        return ['html', 'docx', 'pdf'];
    }
}
