<?php

namespace PhpExcel\Xlsx;

use PhpExcel\Abstract\ArchiveFile;
use Override;

class ContentTypes extends ArchiveFile
{
    #[Override]
    public function getArchiveRelativePath(): string
    {
        return '[Content_Types].xml';
    }
}