<?php

namespace PhpExcel\Xlsx\Rels;

use PhpExcel\Abstract\ArchiveFile;
use Override;

class Rels extends ArchiveFile
{
    #[Override]
    public function getArchiveRelativePath(): string
    {
        return '_rels/.rels';
    }
}