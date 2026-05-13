<?php

namespace PhpExcel\Xlsx\Xl\Worksheets;

use Override;
use PhpExcel\Abstract\ArchiveFile;
use PhpExcel\Abstract\CellUtils;
use PhpExcel\Utils\FileTypes;
use PhpExcel\Utils\Path;
use PhpExcel\Utils\RelationshipTypes;
use RuntimeException;
use SimpleXMLElement;

class Worksheet extends ArchiveFile 
{
    use CellUtils;

    public string $name = '';

    /** @var Row[] */
    protected array $rows = [];

    /** @var array<string,Cell> */
    protected array $cellMap = [];

    #[Override]
    public function getFileType(): ?FileTypes
    {
        return FileTypes::WORKSHEET;
    }

    #[Override]
    public function getRelationshipType(): ?RelationshipTypes
    {
        return RelationshipTypes::WORKSHEET;
    }

    #[Override]
    public function dataChanged()
    {
        $rows = $this->xml->xpath('/x:worksheet/x:sheetData/x:row');
        foreach ($rows as $rowXml) {
            $row = new Row($rowXml);
            $this->rows[$row->number] = $row;
        }

        $this->reloadIndex();
    }

    public function reloadIndex() {
        foreach ($this->rows as $row) {
            foreach ($row->cells as $cell) {
                $this->cellMap[$cell->region] = $cell;
            }
        }
    }

    public function getCell(string $cellName): ?Cell {
        return $this->cellMap[$cellName] ?? null;
    }

    #[Override]
    protected function getDefaultXml(): ?SimpleXMLElement
    {
        return Path::readXMLFile('defaultSheet.xml');
    }

    #[Override]
    protected function refreshXMLAttributes()
    {
        foreach ($this->rows as $row)
            $row->refreshXMLAttributes();
    }

    protected function createNewRow(int $number): Row {
        $sheetData = $this->getXML()->xpath('/x:worksheet/x:sheetData')[0] ?? null;
        if (!$sheetData)
            throw new RuntimeException('Could not resolve sheetData');

        $rowElement = $sheetData->addChild('row');
        foreach (Row::DEFAULT_XML_ATTRIBUTES as $attr => $value)
            $rowElement[$attr] = $value;

        $rowObject = new Row($rowElement);
        $rowObject->number = $number;
        $this->rows[$number] = $rowObject;
        return $rowObject;
    }

    public function write(string $cellName, string $valueOrFormula): Cell {
        list($column, $row) = $this->parseAddress($cellName)->toArray();
        if (!array_key_exists($row, $this->rows))
            $this->createNewRow($row);

        $cell = $this->rows[$row]->write($column, $valueOrFormula);
        $this->cellMap[$cellName] = $cell;
        return $cell;
    }
}