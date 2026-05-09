<?php

namespace PhpExcel\Xlsx\DocProps;

use PhpExcel\Abstract\ArchiveFile;
use Override;

class Core extends ArchiveFile {
    #[Override]
    public function getArchiveRelativePath(): string
    {
        return 'docProps/core.xml';
    }
}