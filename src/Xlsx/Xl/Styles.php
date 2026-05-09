<?php

namespace PhpExcel\Xlsx\Xl;

use PhpExcel\Abstract\ArchiveFile;
use Override;

class Styles extends ArchiveFile {
    #[Override]
    public function getArchiveRelativePath(): string
    {
        return 'xl/styles.xml';
    }
}