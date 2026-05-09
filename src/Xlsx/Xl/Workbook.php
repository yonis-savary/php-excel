<?php

namespace PhpExcel\Xlsx\Xl;

use PhpExcel\Abstract\ArchiveFile;
use Override;

class Workbook extends ArchiveFile {

    public $sheetMap = [];

    #[Override]
    public function getArchiveRelativePath(): string
    {
        return 'xl/workbook.xml';
    }

    #[Override]
    public function dataChanged()
    {
        $newSheetMap = [];
        $sheets = $this->xml->xpath('/x:workbook/x:sheets/x:sheet');
        foreach ($sheets as $sheet) {
            $newSheetMap[(string) $sheet['name']] = "xl/worksheets/sheet" . ((string) $sheet['sheetId']) . ".xml";
        }

        $this->sheetMap = $newSheetMap;
    }
}