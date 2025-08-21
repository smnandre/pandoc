<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests\Unit\Exception;

use Pandoc\Exception\TemplateNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateNotFoundException::class)]
final class TemplateNotFoundExceptionTest extends TestCase
{
    public function testConstructorWithTemplatePath(): void
    {
        $exception = new TemplateNotFoundException('custom-template.latex');

        $this->assertStringContainsString('Template not found: custom-template.latex', $exception->getMessage());
        $this->assertSame('custom-template.latex', $exception->getTemplatePath());
    }
}
