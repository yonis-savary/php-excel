<?php

namespace PhpExcel\Xlsx;

use Override;
use PhpExcel\Abstract\ArchiveFile;
use PhpExcel\Utils\FileTypes;
use PhpExcel\Utils\Path;
use SimpleXMLElement;

class Rels extends ArchiveFile
{
    protected int $lastId = 1;

    public function __construct()
    {
        $this->setData($this->getDefaultXml());
    }

    public static function relsOf(ArchiveFile $sourceFile) {

        $basename = basename($sourceFile->getArchiveRelativePath());
        $dirname = dirname($sourceFile->getArchiveRelativePath());
        return static::fromFile($dirname . '/_rels/' . $basename. '.rels');
    }

    #[Override]
    public function dataChanged()
    {
        $relationships = $this->xml->xpath('/x:Relationships')[0] ?? null;

        $relationshipsLastId = collect($relationships->Relationship)
        ->map(fn($element) => $element['Id'])
        ->max();

        if ($relationshipsLastId)
            $this->lastId = (int) preg_replace('~[^0-9]~', '', $relationshipsLastId);
    }

    #[Override]
    public function getDefaultXml(): ?SimpleXMLElement
    {
        return Path::readXMLFile('defaultRels.xml');
    }

    public function addRef(ArchiveFile $ref) {
        $relationships = $this->xml->xpath('/x:Relationships')[0] ?? null;
        $newRel = $relationships->addChild('Relationship');

        $newId = 'rId' . ($this->lastId++);
        $newRel['Id'] = $newId;

        $thisDirName = substr(dirname($this->originalFilename), 0, -5); // removes '_rels' at the end;
        $targetPath = $ref->getArchiveRelativePath();
        $targetRelPath = str_replace($thisDirName, '', $targetPath);

        $newRel['Target'] = $targetRelPath;
        if ($type = $ref->getRelationshipType()) {
            $newRel['Type'] = $type->value;
        }

        return $newId;
    }

    public function resolveId(string $id): ?string {
        $relationships = $this->getXML()->xpath('/x:Relationships/x:Relationship');

        foreach ($relationships as $relationship) {
            if ($id === $relationship['Id'])
                return basename($this->originalFilename) . '/' . $relationship['Target'];
        }

        return null;
    }

    #[Override]
    public function getFileType(): ?FileTypes
    {
        return FileTypes::RELS;
    }
}