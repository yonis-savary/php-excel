<?php

namespace Tests\Utils;

use PhpExcel\Xlsx\SpreadSheet;

class SpreadsheetExporter
{
    public function __invoke(SpreadSheet $spreadsheet, string $directory)
    {
        if (str_starts_with($directory, '/'))
            $directory = preg_replace('/\/$/', '', $directory);

        foreach ($spreadsheet->toArray() as $relFile => $content) {
            $absolutePath = $directory . '/' . $relFile;
            $absoluteDir = dirname($absolutePath);
            if (!is_dir($absoluteDir))
                mkdir($absoluteDir, recursive: true);

            file_put_contents($absolutePath, $content);
        }
    }
}