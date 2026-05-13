<?php

namespace PhpExcel\Xlsx;

use PhpExcel\Abstract\ArchiveFile;
use Override;
use PhpExcel\Utils\Path;
use SimpleXMLElement;

class ContentTypes extends ArchiveFile
{
    #[Override]
    public function getArchiveRelativePath(): string
    {
        return '[Content_Types].xml';
    }

    #[Override]
    public function getDefaultXml(): ?SimpleXMLElement
    {
        return Path::readXMLFile('defaultContentTypes.xml');
    }

    #[Override]
    public function refreshXMLAttributes()
    {
        $types = $this->xml->xpath('/x:Types')[0];
        unset($types->Override);

        foreach ($this->spreadsheet->elements as $element) {
            if (! $fileType = $element->getFileType()) 
                continue;

            $newOverride = $types->addChild('Override');
            $newOverride->addAttribute('PartName', '/' . $element->getArchiveRelativePath());
            $newOverride->addAttribute('ContentType', $fileType->value);
        }
    }
}