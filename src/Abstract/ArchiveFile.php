<?php

namespace PhpExcel\Abstract;

use PhpExcel\Abstract\XMLHolder;
use PhpExcel\Utils\FileTypes;
use PhpExcel\Utils\RelationshipTypes;
use PhpExcel\Xlsx\SpreadSheet;

class ArchiveFile extends XMLHolder
{
    protected string $originalFilename;
    protected ?SpreadSheet $spreadsheet = null;

    public ?string $relId = null;

    public static function fromFile(string $originalFilename, ?string $content = null): static {
        $instance = new static;
        $instance->originalFilename = $originalFilename;
        if ($content)
            $instance->readString($content);
        return $instance;
    }

    public function getArchiveRelativePath(): string {
        return $this->originalFilename;
    }

    public function setFilename(string $filename) {
        $this->originalFilename = $filename;
    }

    public function getFileType(): ?FileTypes
    {
        return null;
    }

    public function getRelationshipType(): ?RelationshipTypes
    {
        return null;
    }

    public function setSpreadsheet(SpreadSheet $spreadsheet) {
        $this->spreadsheet = $spreadsheet;
    }

    public function addChildToSpreadsheet(SpreadSheet $spreadsheet): void {
        
    }
}