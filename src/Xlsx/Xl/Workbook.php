<?php

namespace PhpExcel\Xlsx\Xl;

use PhpExcel\Abstract\ArchiveFile;
use Override;
use PhpExcel\Utils\FileTypes;
use PhpExcel\Utils\Path;
use PhpExcel\Utils\RelationshipTypes;
use PhpExcel\Xlsx\Rels;
use PhpExcel\Xlsx\SpreadSheet;
use PhpExcel\Xlsx\Xl\Worksheets\Worksheet;
use SimpleXMLElement;

class Workbook extends ArchiveFile {

    protected Rels $rels;
    public $sheetMap = [];

    public int $lastSheetId = 1;

    public function __construct()
    {
        $this->rels = Rels::relsOf($this);
        $this->setData($this->getDefaultXml());
    }

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
            $newSheetMap[(string) $sheet['name']] = $this->rels->resolveId($sheet['r:id']);

            $this->lastSheetId = max($this->lastSheetId, (int) $sheet['sheetId']);
        }

        $this->sheetMap = $newSheetMap;
    }

    #[Override]
    public function getDefaultXml(): ?SimpleXMLElement
    {
        return Path::readXMLFile('defaultWorkbook.xml');
    }

    #[Override]
    public function addChildToSpreadsheet(SpreadSheet $spreadsheet): void
    {
        $spreadsheet->addElement($this->rels);
    }

    public function addSheet(Worksheet $sheet) {
        $newId = $this->rels->addRef($sheet);
        $sheets = $this->xml->xpath('/x:workbook/x:sheets')[0];
        $newSheetXml = $sheets->addChild('sheet');
        $newSheetXml['name'] = $sheet->name;
        $newSheetXml['sheetId'] = $this->lastSheetId++;
        $newSheetXml['state'] = 'visible';
        $newSheetXml['r:id'] = $newId;
    }

    #[Override]
    public function getFileType(): ?FileTypes
    {
        return FileTypes::WORKBOOK;
    }

    #[Override]
    public function getRelationshipType(): ?RelationshipTypes
    {
        return RelationshipTypes::WORKBOOK;
    }
}