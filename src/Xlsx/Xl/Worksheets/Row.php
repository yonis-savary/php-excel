<?php

namespace PhpExcel\Xlsx\Xl\Worksheets;

use PhpExcel\Abstract\XMLHolder;
use SimpleXMLElement;

class Row extends XMLHolder
{
    /** @var Cell[] */
    public array $cells = [];

    public int $number;

    public function __construct(?SimpleXMLElement $xml = null)
    {
        if (!$xml)
            return;

        $this->setData($xml);
        $this->number = $this->getAttribute('r');

        foreach ($xml->c as $cellXml) {
            $cell = new Cell($cellXml);
            $this->cells[$cell->column] = $cell;
        }
    }

    public function write(string $column, string $valueOrFormula): Cell 
    {
        if (!array_key_exists($column, $this->cells)) {
            $cell = new Cell();
            $cell->column = $column;
            $cell->region = $column . $this->number;
            $cell->row = $this->number;
            $this->cells[$column] = $cell;
        }

        $cell = &$this->cells[$column];
        if (str_starts_with($valueOrFormula, '=')) {
            $cell->formula = substr($valueOrFormula, 1);
        } else {
            $cell->value = $valueOrFormula;
        }

        return $cell;
    }
}