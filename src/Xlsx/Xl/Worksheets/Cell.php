<?php

namespace PhpExcel\Xlsx\Xl\Worksheets;

use PhpExcel\Abstract\XMLHolder;
use PhpExcel\Processing\Expression;
use SimpleXMLElement;

class Cell extends XMLHolder {

    public ?string $region;
    public ?string $size;
    public ?string $type;
    public ?string $value;
    public ?string $formula;

    public ?Expression $cachedExpression = null;

    public function __construct(SimpleXMLElement $cellData)
    {
        $this->setData($cellData);
        $this->region = $this->getAttribute('r');
        $this->size = $this->getAttribute('s');
        $this->type = $this->getAttribute('t');
        $this->value = $this->xml->v;
        $this->formula = $this->xml->f;
    }
}