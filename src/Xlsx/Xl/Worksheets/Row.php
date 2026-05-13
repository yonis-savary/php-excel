<?php

namespace PhpExcel\Xlsx\Xl\Worksheets;

use Override;
use PhpExcel\Abstract\XMLHolder;
use SimpleXMLElement;

class Row extends XMLHolder
{
    const DEFAULT_XML_ATTRIBUTES = [
        'customFormat' => "false",
        'ht' => "12.8",
        'hidden' => "false",
        'customHeight' => "false",
        'outlineLevel' => "0" ,
        'collapsed' => "false",
    ];

    /** @var Cell[] */
    public array $cells = [];

    public int $number;

    public function __construct(SimpleXMLElement $xml)
    {
        $this->setData($xml);
        if ($number = $this->getAttribute('r'))
            $this->number = $number;

        foreach ($xml->c as $cellXml) {
            $cell = new Cell($cellXml);
            $this->cells[$cell->column] = $cell;
        }
    }

    #[Override]
    public function refreshXMLAttributes()
    {
        $this->xml['r'] = $this->number;

        foreach ($this->cells as $c) {
            $c->refreshXMLAttributes();
        }
    }

    protected function createCell(string $column): Cell {
        $cellXml = $this->getXML()->addChild('c');
        $cell = new Cell($cellXml);
        $cell->column = $column;
        $cell->region = $column . $this->number;
        $cell->row = $this->number;
        $cell->type = null;
        $cell->size = null;
        $this->cells[$column] = $cell;

        return $cell;
    }

    public function write(string $column, string $valueOrFormula): Cell
    {
        if (!array_key_exists($column, $this->cells))
            $this->createCell($column);

        $cell = $this->cells[$column];
        if (str_starts_with($valueOrFormula, '=')) {
            $cell->formula = substr($valueOrFormula, 1);
        } else {
            $cell->value = $valueOrFormula;
        }

        return $cell;
    }
}