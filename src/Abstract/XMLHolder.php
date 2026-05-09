<?php

namespace PhpExcel\Abstract;

use SimpleXMLElement;

abstract class XMLHolder
{
    protected ?SimpleXMLElement $xml = null;

    public function readFile(string $file) {
        $this->setData(simplexml_load_file($file));
    }

    public function readString(string $data) {
        $this->setData(simplexml_load_string($data));
    }

    public function dataChanged() {}

    public static function fromString(string $string): static {
        $instance = new static();
        $instance->readString($string);
        return $instance;
    }

    public function toString() {
        return $this->xml->asXML();
    }


    public function setData(SimpleXMLElement $xml) {
        $this->xml = $xml;

        $namespaces = $this->xml->getNamespaces(true);

        foreach ($namespaces as $prefix => $namespace) {
            $this->xml->registerXPathNamespace(
                $prefix ?: 'x',
                $namespace
            );
        }

        $this->dataChanged();
    }


    protected function getAttribute(string $attribute, ?SimpleXMLElement $element = null): ?string {
        $element ??= $this->xml;
        if ($e = $element[$attribute])
            return (string) $e;
        return null;
    }
}