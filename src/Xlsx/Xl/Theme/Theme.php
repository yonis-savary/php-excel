<?php

namespace PhpExcel\Xlsx\Xl\Theme;

use PhpExcel\Abstract\ArchiveFile;
use Override;

class Theme extends ArchiveFile {
    protected string $name = 'default';

    #[Override]
    public function getArchiveRelativePath(): string
    {
        return 'xl/theme/' . $this->name . '.xml';
    }
}