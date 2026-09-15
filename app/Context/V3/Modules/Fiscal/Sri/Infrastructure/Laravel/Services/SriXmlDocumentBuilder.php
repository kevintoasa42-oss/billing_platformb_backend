<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use InvalidArgumentException;
use RuntimeException;

/**
 * Builds SRI XML through PHP's DOM implementation instead of concatenating
 * markup by hand. DOMDocument owns escaping and keeps the document tree
 * structurally valid while the SRI XSD remains responsible for fiscal rules.
 */
final class SriXmlDocumentBuilder
{
    private const INVALID_XML_CHARACTERS = '/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u';

    private readonly DOMDocument $document;

    /** @param array<string, string> $attributes */
    public function __construct(string $rootName, array $attributes = [])
    {
        $this->document = new DOMDocument('1.0', 'UTF-8');
        $this->document->formatOutput = true;
        $this->document->preserveWhiteSpace = false;

        $root = $this->document->createElement($rootName);
        $this->setAttributes($root, $attributes);
        $this->document->appendChild($root);
    }

    public function root(): DOMElement
    {
        /** @var DOMElement $root */
        $root = $this->document->documentElement;

        return $root;
    }

    /** @param array<string, string> $attributes */
    public function append(DOMNode $parent, string $name, array $attributes = []): DOMElement
    {
        $element = $this->document->createElement($name);
        $this->setAttributes($element, $attributes);
        $parent->appendChild($element);

        return $element;
    }

    /** @param array<string, string> $attributes */
    public function appendText(
        DOMNode $parent,
        string $name,
        string $value,
        array $attributes = [],
    ): DOMElement {
        $element = $this->append($parent, $name, $attributes);
        $element->appendChild($this->document->createTextNode($this->assertXmlText($value)));

        return $element;
    }

    public function toXml(): string
    {
        $xml = $this->document->saveXML();
        if ($xml === false) {
            throw new RuntimeException('No se pudo serializar el XML SRI.');
        }

        return $xml;
    }

    /** @param array<string, string> $attributes */
    private function setAttributes(DOMElement $element, array $attributes): void
    {
        foreach ($attributes as $name => $value) {
            $element->setAttribute($name, $this->assertXmlText($value));
        }
    }

    private function assertXmlText(string $value): string
    {
        $cleaned = preg_replace(self::INVALID_XML_CHARACTERS, '', $value);
        if ($cleaned === null) {
            throw new InvalidArgumentException('El texto contiene caracteres XML invalidos.');
        }

        return $cleaned;
    }
}
