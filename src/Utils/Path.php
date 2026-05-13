<?php

namespace PhpExcel\Utils;

use InvalidArgumentException;
use SimpleXMLElement;

class Path
{
    protected static ?string $projectPath = null;

    public static function resolveProjectPath() {
        self::$projectPath = realpath(__DIR__ . '/../..');
    }

    public static function getProjectPath(): string
    {
        if (is_null(self::$projectPath)) {
            self::resolveProjectPath();
        }

        return self::$projectPath;
    }

    public static function normalize(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('/\/{2,}/', '/', $path);
        if ('/' !== $path) {
            $path = preg_replace('/\/$/', '', $path);
        }

        return $path;
    }

    public static function join(string ...$parts): string
    {
        $parts = array_filter($parts);

        return self::normalize(join('/', $parts));
    }

    public static function relative(string $path, ?string $reference = null): string
    {
        $path = self::normalize($path);

        $reference ??= self::getProjectPath();
        $reference = self::normalize($reference);

        if (!str_starts_with($path, $reference)) {
            $path = self::join($reference, $path);
        }

        return self::normalize($path);
    }

    public static function readXMLFile(string $templateName): SimpleXMLElement {
        $path = self::relative("xml/" . $templateName);
        if (!is_file($path))
            throw new InvalidArgumentException("File $path is not a file");

        return new SimpleXMLElement(file_get_contents($path));
    }
}