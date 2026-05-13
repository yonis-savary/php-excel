<?php

namespace PhpExcel\Xlsx\Xl\Worksheets;

use Override;
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

    public function __construct(SimpleXMLElement $cellData)
    {
        $this->setData($cellData);
        if ($region = $this->getAttribute('r')) $this->region = $region;
        if ($size = $this->getAttribute('s')) $this->size = $size;
        if ($type = $this->getAttribute('t')) $this->type = $type;
        if ($value = $this->xml->v) $this->value = $value;
        if ($formula = $this->xml->f) $this->formula = $formula;

        if ($this->region){
            list($this->column, $this->row) = $this->parseAddress($this->region);
        }
    }

    #[Override]
    public function refreshXMLAttributes()
    {
        $this->xml['r'] = $this->region;

        if (!is_null($this->size))
            $this->xml['s'] = $this->size;
        if (!is_null($this->type))
            $this->xml['t'] = $this->type;

        if ($this->value)
            $this->xml->v = $this->value;
        if ($this->formula)
            $this->xml->f = $this->formula;
    }
}