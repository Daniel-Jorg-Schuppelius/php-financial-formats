<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : XmlParserAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts;

use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use RuntimeException;

/**
 * Abstrakte Basisklasse für XML-Parser.
 * 
 * Stellt gemeinsame XML-Parsing-Funktionalität bereit:
 * - DOM/XPath-Initialisierung mit Namespace-Handling
 * - XPath-Hilfsmethoden (xpathString, xpathStringWithFallback)
 * - Datentyp-Konvertierung (parseAmount, parseDateTime)
 * - Ressourcen-Verwaltung
 * 
 * Unterklassen implementieren format-spezifische Parsing-Logik.
 */
abstract class XmlParserAbstract {
    protected DOMDocument $dom;
    protected DOMXPath $xpath;
    protected ?string $namespace = null;
    protected string $prefix = '';

    // =========================================================================
    // KONSTRUKTOR & INITIALISIERUNG
    // =========================================================================

    /**
     * Erstellt einen neuen Parser aus XML-Inhalt.
     * 
     * @param string $xmlContent Der XML-Inhalt
     * @throws RuntimeException Bei ungültigem XML
     */
    public function __construct(string $xmlContent) {
        $this->initializeDom($xmlContent);
    }

    /**
     * Initialisiert DOM und XPath aus XML-Inhalt.
     * 
     * @param string $xmlContent Der XML-Inhalt
     * @throws RuntimeException Bei ungültigem XML
     */
    protected function initializeDom(string $xmlContent): void {
        $this->dom = new DOMDocument();
        libxml_use_internal_errors(true);

        if (!$this->dom->loadXML($xmlContent)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new RuntimeException(
                "Ungültiges XML-Dokument: " . ($errors[0]->message ?? 'Unbekannter Fehler')
            );
        }

        libxml_clear_errors();

        $this->xpath = new DOMXPath($this->dom);
        $this->namespace = $this->detectNamespace();

        if (!empty($this->namespace)) {
            $this->registerNamespace($this->getNamespacePrefix(), $this->namespace);
            $this->prefix = $this->getNamespacePrefix() . ':';
        }
    }

    /**
     * Erkennt den Namespace des XML-Dokuments.
     * Kann von Unterklassen überschrieben werden für format-spezifische Logik.
     * 
     * @return string|null Der erkannte Namespace oder null
     */
    protected function detectNamespace(): ?string {
        $root = $this->dom->documentElement;
        if (!$root) {
            return null;
        }

        // Prüfe namespaceURI des Root-Elements
        if (!empty($root->namespaceURI)) {
            return $root->namespaceURI;
        }

        // Prüfe xmlns-Attribut
        if ($root->hasAttribute('xmlns')) {
            return $root->getAttribute('xmlns');
        }

        return null;
    }

    /**
     * Gibt den zu verwendenden Namespace-Prefix zurück.
     * Kann von Unterklassen überschrieben werden.
     * 
     * @return string Der Namespace-Prefix (z.B. 'ns', 'p', 'camt')
     */
    protected function getNamespacePrefix(): string {
        return 'ns';
    }

    /**
     * Registriert einen Namespace im XPath.
     * 
     * @param string $prefix Der Namespace-Prefix
     * @param string $namespace Die Namespace-URI
     */
    protected function registerNamespace(string $prefix, string $namespace): void {
        $this->xpath->registerNamespace($prefix, $namespace);
    }

    // =========================================================================
    // XPATH HELPER METHODEN
    // =========================================================================

    /**
     * Evaluiert einen XPath-Ausdruck und gibt einen String oder null zurück.
     * Fügt automatisch string(...) hinzu, wenn nicht vorhanden.
     * 
     * @param string $expression XPath-Ausdruck
     * @param DOMNode|null $context Kontext-Node (optional)
     * @return string|null Ergebnis oder null wenn leer
     */
    protected function xpathString(string $expression, ?DOMNode $context = null): ?string {
        // Automatisch string() hinzufügen, wenn nicht vorhanden
        if (!str_starts_with($expression, 'string(')) {
            $expression = "string({$expression})";
        }

        $result = $context !== null
            ? $this->xpath->evaluate($expression, $context)
            : $this->xpath->evaluate($expression);

        return !empty($result) ? (string)$result : null;
    }

    /**
     * Evaluiert einen XPath-Ausdruck mit Fallback-Alternativen.
     * 
     * @param array<string> $expressions Liste von XPath-Ausdrücken
     * @param DOMNode|null $context Kontext-Node (optional)
     * @return string|null Erstes nicht-leeres Ergebnis oder null
     */
    protected function xpathStringWithFallback(array $expressions, ?DOMNode $context = null): ?string {
        foreach ($expressions as $expression) {
            $result = $this->xpathString($expression, $context);
            if ($result !== null) {
                return $result;
            }
        }
        return null;
    }

    /**
     * Findet einen Node via XPath.
     * 
     * @param string $expression XPath-Ausdruck
     * @param DOMNode|null $context Kontext-Node (optional)
     * @return DOMNode|null Der gefundene Node oder null
     */
    protected function findNode(string $expression, ?DOMNode $context = null): ?DOMNode {
        $result = $context !== null
            ? $this->xpath->query($expression, $context)
            : $this->xpath->query($expression);

        return ($result && $result->length > 0) ? $result->item(0) : null;
    }

    /**
     * Findet einen erforderlichen Node via XPath.
     * 
     * @param string $expression XPath-Ausdruck
     * @param string $errorMessage Fehlermeldung wenn nicht gefunden
     * @param DOMNode|null $context Kontext-Node (optional)
     * @return DOMNode Der gefundene Node
     * @throws RuntimeException Wenn Node nicht gefunden
     */
    protected function findRequiredNode(string $expression, string $errorMessage, ?DOMNode $context = null): DOMNode {
        $node = $this->findNode($expression, $context);
        if ($node === null) {
            throw new RuntimeException($errorMessage);
        }
        return $node;
    }

    /**
     * Findet alle Nodes via XPath.
     * 
     * @param string $expression XPath-Ausdruck
     * @param DOMNode|null $context Kontext-Node (optional)
     * @return DOMNodeList<DOMNode> Die gefundenen Nodes
     */
    protected function findNodes(string $expression, ?DOMNode $context = null): DOMNodeList {
        return $context !== null
            ? $this->xpath->query($expression, $context)
            : $this->xpath->query($expression);
    }

    // =========================================================================
    // DATENTYP-KONVERTIERUNG
    // =========================================================================

    /**
     * Parst einen Betrag aus einem String.
     * 
     * @param string|null $amountStr Betrags-String
     * @return float Betrag als Float
     */
    protected function parseAmount(?string $amountStr): float {
        if ($amountStr === null || $amountStr === '') {
            return 0.0;
        }
        return (float) str_replace(',', '.', $amountStr);
    }

    /**
     * Parst einen Betrag mit Währung aus einem Element mit Ccy-Attribut.
     * 
     * @param string $amountPath XPath zum Betrags-Element
     * @param DOMNode $context Kontext-Node
     * @param CurrencyCode $default Standard-Währung
     * @return array{amount: float, currency: CurrencyCode}
     */
    protected function parseAmountWithCurrency(
        string $amountPath,
        DOMNode $context,
        CurrencyCode $default = CurrencyCode::Euro
    ): array {
        $amtNode = $this->xpath->query($amountPath, $context)->item(0);

        $amount = 0.0;
        $currency = $default;

        if ($amtNode instanceof DOMElement) {
            $amount = $this->parseAmount($amtNode->textContent);
            $currencyStr = $amtNode->getAttribute('Ccy') ?: 'EUR';
            $currency = CurrencyCode::tryFrom($currencyStr) ?? $default;
        }

        return ['amount' => $amount, 'currency' => $currency];
    }

    /**
     * Parst einen DateTime-String zu DateTimeImmutable.
     * 
     * @param string|null $dateTimeStr Der DateTime-String
     * @param DateTimeImmutable|null $default Standardwert wenn leer
     * @return DateTimeImmutable|null
     */
    protected function parseDateTime(?string $dateTimeStr, ?DateTimeImmutable $default = null): ?DateTimeImmutable {
        if (empty($dateTimeStr)) {
            return $default;
        }
        return new DateTimeImmutable($dateTimeStr);
    }

    /**
     * Konvertiert leere Strings zu null.
     * 
     * @param string $value Der String
     * @return string|null Null wenn leer, sonst der String
     */
    protected function emptyToNull(string $value): ?string {
        return $value !== '' ? $value : null;
    }

    // =========================================================================
    // RESSOURCEN-VERWALTUNG
    // =========================================================================

    /**
     * Bereinigt libxml-Ressourcen.
     * Sollte am Ende des Parsens aufgerufen werden.
     */
    protected function cleanup(): void {
        libxml_clear_errors();
    }

    /**
     * Gibt das DOM-Dokument zurück.
     */
    public function getDom(): DOMDocument {
        return $this->dom;
    }

    /**
     * Gibt das XPath-Objekt zurück.
     */
    public function getXPath(): DOMXPath {
        return $this->xpath;
    }

    /**
     * Gibt den aktuellen Namespace zurück.
     */
    public function getNamespace(): ?string {
        return $this->namespace;
    }

    /**
     * Gibt den aktuellen Prefix zurück.
     */
    public function getPrefix(): string {
        return $this->prefix;
    }
}