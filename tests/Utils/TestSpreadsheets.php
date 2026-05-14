<?php

namespace Tests\Utils;

use InvalidArgumentException;
use PhpExcel\Xlsx\SpreadSheet;
use PHPUnit\Framework\TestCase;

trait TestSpreadsheets
{
    protected function fixtureDirectoryToArray(string $directory, ?string $commonKeyPrefix = null): array {
        $entries = [];
        $commonKeyPrefix ??= $directory;

        foreach (scandir($directory) as $fileOrDir) {
            if ($fileOrDir === '.' || $fileOrDir === '..')
                continue;

            $path = $directory . '/' . $fileOrDir;
            if (is_dir($path))
                $entries = array_merge($entries, $this->fixtureDirectoryToArray($path, $commonKeyPrefix));
            else
                $entries[str_replace($commonKeyPrefix . '/', '', $path)] = file_get_contents($path);
        }

        return $entries;
    }

    public function readFixture(string $fixtureName): array {
        $path = realpath(__DIR__ . "/../Fixtures/$fixtureName");
        if (!is_dir($path))
            throw new InvalidArgumentException("Cannot read $fixtureName");

        $entries = $this->fixtureDirectoryToArray($path);
        ksort($entries);
        return $entries;
    }
}