<?php

namespace PhpExcel\Xlsx\Xl\Worksheets;

use PhpExcel\Abstract\CellUtils;
use PhpExcel\Abstract\XMLHolder;
use PhpExcel\Processing\Expressions\Expression;
use SimpleXMLElement;

class Cell extends XMLHolder {
    use CellUtils;

    public ?string $column = null;
    public ?string $row = null;

    public ?string $region = null;
    public ?string $size = null;
    public ?string $type = null;
    public ?string $value = null;
    public ?string $formula = null;

    public ?Expression $cachedExpression = null;

    public function __construct(?SimpleXMLElement $cellData = null)
    {
        if (!$cellData)
            return;

        $this->setData($cellData);
        $this->region = $this->getAttribute('r');
        $this->size = $this->getAttribute('s');
        $this->type = $this->getAttribute('t');
        $this->value = $this->xml->v;
        $this->formula = $this->xml->f;

        list(
            $this->column,
            $this->row
        ) = $this->parseAddress($this->region);
    }
}