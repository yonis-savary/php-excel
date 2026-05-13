<?php

namespace PhpExcel\Abstract;

use RuntimeException;
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
        return $this->getXML()->asXML();
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

    protected function getDefaultXml(): ?SimpleXMLElement {
        return null;
    }

    public function &getXML(): SimpleXMLElement {
        if (is_null($this->xml)){
            $this->setData($this->getDefaultXml());
            if (is_null($this->xml))
                throw new RuntimeException('No xml content nor default xml defined for this class');
        }
        $this->refreshXMLAttributes();
        return $this->xml;
    }

    protected function refreshXMLAttributes() {}

    protected function getAttribute(string $attribute, ?SimpleXMLElement $element = null): ?string {
        $element ??= $this->xml;
        if ($e = $element[$attribute])
            return (string) $e;
        return null;
    }
}