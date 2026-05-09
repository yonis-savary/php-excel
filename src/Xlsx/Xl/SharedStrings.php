<?php

namespace PhpExcel\Xlsx\Xl;

use PhpExcel\Abstract\ArchiveFile;
use Override;

class SharedStrings extends ArchiveFile {
    #[Override]
    public function getArchiveRelativePath(): string
    {
        return 'xl/sharedStrings.xml';
    }
}