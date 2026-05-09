<?php

namespace PhpExcel\Xlsx\Xl\Worksheets;

use PhpExcel\Abstract\XMLHolder;
use SimpleXMLElement;

class Row extends XMLHolder
{
    /** @var Cell[] */
    public array $cells = [];

    public readonly int $number;

    public function __construct(SimpleXMLElement $xml)
    {
        $this->setData($xml);
        $this->number = $this->getAttribute('r');

        foreach ($xml->c as $cellXml) {
            $cell = new Cell($cellXml);
            $this->cells[$cell->region] = $cell;
        }
    }
}