<?php

namespace PhpExcel\Xlsx\DocProps;

use PhpExcel\Abstract\ArchiveFile;
use Override;

class App extends ArchiveFile {
    #[Override]
    public function getArchiveRelativePath(): string
    {
        return 'docProps/app.xml';
    }
}