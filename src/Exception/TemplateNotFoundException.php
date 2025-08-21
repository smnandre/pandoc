<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Exception;

/**
 * Thrown when template file is not found.
 */
final class TemplateNotFoundException extends PandocException
{
    private string $templatePath;

    public function __construct(string $templatePath, int $code = 0, ?\Throwable $previous = null)
    {
        $this->templatePath = $templatePath;

        $message = "Template not found: {$templatePath}";

        parent::__construct($message, $code, $previous);
    }

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }
}
