<?php

namespace PhpExcel\Abstract;

use PhpExcel\Abstract\XMLHolder;

class ArchiveFile extends XMLHolder
{
    protected string $originalFilename;

    public static function fromFile(string $originalFilename, string $content): static {
        $instance = new static;
        $instance->originalFilename = $originalFilename;
        $instance->readString($content);
        return $instance;
    }

    public function getArchiveRelativePath(): string {
        return $this->originalFilename;
    }
}