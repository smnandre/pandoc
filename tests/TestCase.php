<?php

/*
 * This file is part of the smnandre/pandoc package.
 *
 * (c) Simon Andre <smn.andre@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pandoc\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getFixturesDirectory(): string
    {
        return __DIR__ . '/Fixtures';
    }

    protected function getTemporaryDirectory(): string
    {
        return sys_get_temp_dir() . '/pandoc-test';
    }

    protected function createTemporaryFile(string $content = ''): string
    {
        $tempDir = $this->getTemporaryDirectory();
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $file = tempnam($tempDir, 'pandoc-test-');
        file_put_contents($file, $content);

        return $file;
    }

    protected function createTemporaryDirectory(): string
    {
        $tempDir = $this->getTemporaryDirectory();
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        return $tempDir;
    }

    protected function cleanupTemporaryDirectory(): void
    {
        $tempDir = $this->getTemporaryDirectory();
        if (is_dir($tempDir)) {
            $files = glob($tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($tempDir);
        }
    }
}
