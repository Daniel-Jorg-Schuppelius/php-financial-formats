<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : XmlDocumentExportTrait.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Traits;

use CommonToolkit\Contracts\Interfaces\XML\XmlElementInterface;
use CommonToolkit\Entities\XML\Document as XmlDocument;
use CommonToolkit\Helper\FileSystem\File;
use DOMDocument;
use DOMNode;

/**
 * Trait für XML-Dokument-Export.
 * 
 * Implementiert XmlDocumentInterface-Methoden für CAMT/Pain Dokumente.
 * 
 * Voraussetzung: Die Klasse muss eine getDefaultXml(): string Methode bereitstellen,
 * die das XML mit Standard-Parametern generiert.
 */
trait XmlDocumentExportTrait {
    /**
     * Gecachtes XmlDocument für Interface-Methoden.
     */
    private ?XmlDocument $cachedXmlDocument = null;

    /**
     * Muss von der implementierenden Klasse bereitgestellt werden.
     * Generiert das XML mit den Standard-Parametern.
     */
    abstract protected function getDefaultXml(): string;

    /**
     * Gibt das Dokument als CommonToolkit XmlDocument zurück.
     */
    public function toXmlDocument(): XmlDocument {
        if ($this->cachedXmlDocument === null) {
            $xml = $this->getDefaultXml();
            $this->cachedXmlDocument = XmlDocument::fromString($xml);
        }
        return $this->cachedXmlDocument;
    }

    /**
     * @inheritDoc
     */
    public function getVersion(): string {
        return '1.0';
    }

    /**
     * @inheritDoc
     */
    public function getEncoding(): string {
        return 'UTF-8';
    }

    /**
     * @inheritDoc
     */
    public function getRootElement(): XmlElementInterface {
        return $this->toXmlDocument()->getRootElement();
    }

    /**
     * @inheritDoc
     */
    public function toDomDocument(): DOMDocument {
        return $this->toXmlDocument()->toDomDocument();
    }

    /**
     * @inheritDoc
     */
    public function toDomNode(DOMDocument $doc): DOMNode {
        return $doc->importNode($this->toDomDocument()->documentElement, true);
    }

    /**
     * @inheritDoc
     */
    public function toString(): string {
        return $this->getDefaultXml();
    }

    /**
     * @inheritDoc
     */
    public function toFile(string $filePath): void {
        File::write($filePath, $this->toString());
    }

    /**
     * @inheritDoc
     */
    public function validateAgainstXsd(string $xsdFile): array {
        return $this->toXmlDocument()->validateAgainstXsd($xsdFile);
    }

    /**
     * Invalidiert den Cache.
     */
    protected function invalidateXmlCache(): void {
        $this->cachedXmlDocument = null;
    }
}
