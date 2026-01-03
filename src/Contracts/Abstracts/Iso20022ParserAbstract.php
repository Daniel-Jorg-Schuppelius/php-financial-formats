<?php
/*
 * Created on   : Thu Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Iso20022ParserAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts;

use CommonToolkit\Contracts\Abstracts\XML\XmlParserAbstract;
use CommonToolkit\Entities\XML\ExtendedDOMDocument;
use CommonToolkit\Enums\CountryCode;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PaymentIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PostalAddress;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\RemittanceInformation;
use CommonToolkit\Parsers\ExtendedDOMDocumentParser;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use RuntimeException;

/**
 * Abstract base class for ISO 20022 XML parsers (CAMT, Pain).
 * 
 * Extends XmlParserAbstract with ISO 20022-specific functionality:
 * - Namespace detection for ISO 20022 URNs
 * - Gemeinsame Party/Account-Parsing-Methoden
 * - CreditDebit-Handling
 * - Balance/Transaction-Basis-Parsing
 * 
 * Provides both instance and static methods for flexibility.
 */
abstract class Iso20022ParserAbstract extends XmlParserAbstract {
    // =========================================================================
    // KONSTANTEN
    // =========================================================================

    /**
     * ISO 20022 URN prefix for namespace detection.
     */
    protected const ISO20022_URN_PREFIX = 'urn:iso:std:iso:20022:tech:xsd:';

    // =========================================================================
    // NAMESPACE-HANDLING (STATISCH)
    // =========================================================================

    /**
     * Creates an ExtendedDOMDocument for an ISO 20022 document.
     * 
     * @param string $xmlContent Der XML-Inhalt
     * @param string $formatType The format type for namespace detection (e.g. 'camt.053', 'pain.001')
     * @param array<string> $knownNamespaces Optional: List of known namespaces for this type
     * @return array{doc: ExtendedDOMDocument, prefix: string}
     * @throws RuntimeException On invalid XML
     */
    protected static function createIso20022Document(
        string $xmlContent,
        string $formatType,
        array $knownNamespaces = []
    ): array {
        $doc = ExtendedDOMDocumentParser::fromString($xmlContent);

        // Namespace erkennen und registrieren
        $namespace = static::detectIso20022Namespace($doc, $formatType, $knownNamespaces);
        $prefix = '';

        if (!empty($namespace)) {
            $doc->registerXPathNamespace('ns', $namespace);
            $prefix = 'ns:';
        }

        return [
            'doc' => $doc,
            'prefix' => $prefix
        ];
    }

    /**
     * Creates DOM and XPath for an ISO 20022 document with namespace handling.
     * 
     * @param string $xmlContent Der XML-Inhalt
     * @param string $formatType The format type for namespace detection (e.g. 'camt.053', 'pain.001')
     * @param array<string> $knownNamespaces Optional: List of known namespaces for this type
     * @return array{dom: DOMDocument, xpath: DOMXPath, namespace: ?string, prefix: string}
     * @throws RuntimeException On invalid XML
     * @deprecated Use createIso20022Document() for ExtendedDOMDocument
     */
    protected static function createIso20022XPath(
        string $xmlContent,
        string $formatType,
        array $knownNamespaces = []
    ): array {
        $doc = ExtendedDOMDocumentParser::fromString($xmlContent);
        $namespace = static::detectIso20022Namespace($doc, $formatType, $knownNamespaces);
        $prefix = '';

        if (!empty($namespace)) {
            $doc->registerXPathNamespace('ns', $namespace);
            $prefix = 'ns:';
        }

        return [
            'dom' => $doc,
            'xpath' => $doc->getXPath(),
            'namespace' => $namespace,
            'prefix' => $prefix
        ];
    }

    /**
     * Erkennt den Namespace eines ISO 20022 Dokuments.
     * 
     * @param DOMDocument $dom Das DOM-Dokument
     * @param string $formatType Der Format-Typ (z.B. 'camt.053', 'pain.001')
     * @param array<string> $knownNamespaces Known namespaces for this type
     * @return string|null Der erkannte Namespace oder null
     */
    protected static function detectIso20022Namespace(
        DOMDocument $dom,
        string $formatType,
        array $knownNamespaces = []
    ): ?string {
        $root = $dom->documentElement;
        if (!$root) {
            return null;
        }

        // 1. Prüfe bekannte Namespaces
        foreach ($knownNamespaces as $namespace) {
            if ($root->namespaceURI === $namespace) {
                return $namespace;
            }
            if ($root->lookupNamespaceUri('') === $namespace) {
                return $namespace;
            }
        }

        // 2. Prüfe namespaceURI des Root-Elements
        $ns = $root->namespaceURI;
        if (!empty($ns) && str_contains($ns, $formatType)) {
            return $ns;
        }

        // 3. Prüfe xmlns-Attribut
        if ($root->hasAttribute('xmlns')) {
            $xmlns = $root->getAttribute('xmlns');
            if (str_contains($xmlns, $formatType)) {
                return $xmlns;
            }
        }

        // 4. Suche in xmlns:XX prefixed namespaces
        foreach ($root->attributes as $attr) {
            if (str_starts_with($attr->name, 'xmlns') && str_contains($attr->value, $formatType)) {
                // Prüfen ob Elemente tatsächlich diesen Namespace verwenden
                $firstChild = $root->firstElementChild;
                if ($firstChild && $firstChild->namespaceURI === $attr->value) {
                    return $attr->value;
                }
            }
        }

        // 5. Fallback: Default-Namespace
        if (!empty($ns)) {
            return $ns;
        }

        return null;
    }

    /**
     * Initializes XPath with optional namespace (backwards compatible).
     * 
     * @param DOMDocument $dom Das DOM-Dokument (oder ExtendedDOMDocument)
     * @param string $formatType Der Format-Typ (z.B. 'camt.053', 'pain.001')
     * @return array{0: DOMXPath, 1: string} [XPath object, namespace prefix]
     * @deprecated Use createIso20022Document() for ExtendedDOMDocument
     */
    protected static function initializeXPath(DOMDocument $dom, string $formatType = ''): array {
        // Wenn bereits ein ExtendedDOMDocument, nutze dessen XPath
        if ($dom instanceof ExtendedDOMDocument) {
            $namespace = static::detectIso20022Namespace($dom, $formatType);
            $prefix = '';

            if (!empty($namespace)) {
                $dom->registerXPathNamespace('ns', $namespace);
                $prefix = 'ns:';
            }

            return [$dom->getXPath(), $prefix];
        }

        $xpath = new DOMXPath($dom);
        $namespace = static::detectIso20022Namespace($dom, $formatType);

        if (!empty($namespace)) {
            $xpath->registerNamespace('ns', $namespace);
            return [$xpath, 'ns:'];
        }

        return [$xpath, ''];
    }

    // =========================================================================
    // CREDIT/DEBIT HANDLING
    // =========================================================================

    /**
     * Parst einen CreditDebit-Indikator.
     * 
     * @param string|null $indicator Der Indikator-String (CRDT/DBIT)
     * @return CreditDebit Das CreditDebit-Enum
     */
    protected static function parseCreditDebitIndicator(?string $indicator): CreditDebit {
        return match (strtoupper($indicator ?? '')) {
            'DBIT' => CreditDebit::DEBIT,
            'CRDT' => CreditDebit::CREDIT,
            default => CreditDebit::CREDIT
        };
    }

    /**
     * Checks the reversal indicator.
     * 
     * @param string|null $indicator Der Indikator-String
     * @return bool True wenn Storno
     */
    protected static function isReversalIndicator(?string $indicator): bool {
        return strtolower($indicator ?? '') === 'true';
    }

    // =========================================================================
    // PARTY-PARSING (STATISCH)
    // =========================================================================

    /**
     * Parst eine PartyIdentification (Name, Adresse, IDs).
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param DOMNode|null $node Der Party-Node
     * @param string $prefix Namespace prefix
     * @return PartyIdentification Die geparste Party
     */
    protected static function parseParty(DOMXPath $xpath, ?DOMNode $node, string $prefix): PartyIdentification {
        if (!$node) {
            return new PartyIdentification(name: 'Unknown');
        }

        $name = $xpath->evaluate("string({$prefix}Nm)", $node);
        $bic = $xpath->evaluate("string({$prefix}Id/{$prefix}OrgId/{$prefix}AnyBIC)", $node);
        $lei = $xpath->evaluate("string({$prefix}Id/{$prefix}OrgId/{$prefix}LEI)", $node);
        $countryOfResidence = $xpath->evaluate("string({$prefix}CtryOfRes)", $node);

        // OrganisationId (Othr)
        $organisationId = $xpath->evaluate("string({$prefix}Id/{$prefix}OrgId/{$prefix}Othr/{$prefix}Id)", $node);

        // PostalAddress parsen (optional)
        $pstlAdrNode = $xpath->query("{$prefix}PstlAdr", $node)->item(0);
        $postalAddress = $pstlAdrNode
            ? static::parsePostalAddr($xpath, $pstlAdrNode, $prefix)
            : null;

        $countryCode = null;
        if (!empty($countryOfResidence)) {
            $countryCode = CountryCode::tryFrom($countryOfResidence);
        }

        return new PartyIdentification(
            name: !empty($name) ? $name : null,
            postalAddress: $postalAddress,
            bic: !empty($bic) ? $bic : null,
            lei: !empty($lei) ? $lei : null,
            countryOfResidence: $countryCode,
            organisationId: !empty($organisationId) ? $organisationId : null
        );
    }

    /**
     * Parst eine PostalAddress.
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param DOMNode $node Der PostalAddress-Node
     * @param string $prefix Namespace prefix
     * @return PostalAddress Die geparste Adresse
     */
    protected static function parsePostalAddr(DOMXPath $xpath, DOMNode $node, string $prefix): PostalAddress {
        $streetName = $xpath->evaluate("string({$prefix}StrtNm)", $node);
        $buildingNumber = $xpath->evaluate("string({$prefix}BldgNb)", $node);
        $postCode = $xpath->evaluate("string({$prefix}PstCd)", $node);
        $townName = $xpath->evaluate("string({$prefix}TwnNm)", $node);
        $countryStr = $xpath->evaluate("string({$prefix}Ctry)", $node);

        $country = !empty($countryStr) ? CountryCode::tryFrom($countryStr) : null;

        // Address lines
        $addressLines = [];
        $adrLineNodes = $xpath->query("{$prefix}AdrLine", $node);
        foreach ($adrLineNodes as $lineNode) {
            $addressLines[] = $lineNode->textContent;
        }

        return new PostalAddress(
            streetName: !empty($streetName) ? $streetName : null,
            buildingNumber: !empty($buildingNumber) ? $buildingNumber : null,
            postCode: !empty($postCode) ? $postCode : null,
            townName: !empty($townName) ? $townName : null,
            country: $country,
            addressLines: $addressLines
        );
    }

    /**
     * Parst eine AccountIdentification.
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param DOMNode|null $node Der Account-Node
     * @param string $prefix Namespace prefix
     * @return AccountIdentification Die geparste Kontoidentifikation
     */
    protected static function parseAccount(DOMXPath $xpath, ?DOMNode $node, string $prefix): AccountIdentification {
        if (!$node) {
            return new AccountIdentification();
        }

        $iban = $xpath->evaluate("string({$prefix}Id/{$prefix}IBAN)", $node);
        $other = $xpath->evaluate("string({$prefix}Id/{$prefix}Othr/{$prefix}Id)", $node);
        $currencyStr = $xpath->evaluate("string({$prefix}Ccy)", $node);

        $currency = !empty($currencyStr) ? (CurrencyCode::tryFrom($currencyStr) ?? null) : null;

        return new AccountIdentification(
            iban: !empty($iban) ? $iban : null,
            other: !empty($other) ? $other : null,
            currency: $currency
        );
    }

    /**
     * Parst eine FinancialInstitution.
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param DOMNode|null $node Der FinancialInstitution-Node
     * @param string $prefix Namespace prefix
     * @return FinancialInstitution Die geparste Institution
     */
    protected static function parseFinancialInst(DOMXPath $xpath, ?DOMNode $node, string $prefix): FinancialInstitution {
        if (!$node) {
            return new FinancialInstitution();
        }

        $finInstnIdNode = $xpath->query("{$prefix}FinInstnId", $node)->item(0);
        if (!$finInstnIdNode) {
            return new FinancialInstitution();
        }

        // BICFI (neuere Versionen) oder BIC (ältere Versionen)
        $bic = $xpath->evaluate("string({$prefix}BICFI)", $finInstnIdNode);
        if (empty($bic)) {
            $bic = $xpath->evaluate("string({$prefix}BIC)", $finInstnIdNode);
        }
        $name = $xpath->evaluate("string({$prefix}Nm)", $finInstnIdNode);
        $lei = $xpath->evaluate("string({$prefix}LEI)", $finInstnIdNode);
        $memberId = $xpath->evaluate("string({$prefix}ClrSysMmbId/{$prefix}MmbId)", $finInstnIdNode);

        return new FinancialInstitution(
            bic: !empty($bic) ? $bic : null,
            name: !empty($name) ? $name : null,
            lei: !empty($lei) ? $lei : null,
            memberId: !empty($memberId) ? $memberId : null
        );
    }

    /**
     * Parst eine PaymentIdentification.
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param DOMNode $node Der PaymentIdentification-Node
     * @param string $prefix Namespace prefix
     * @return PaymentIdentification Die geparste Zahlungsidentifikation
     */
    protected static function parsePaymentId(DOMXPath $xpath, DOMNode $node, string $prefix): PaymentIdentification {
        $instrId = $xpath->evaluate("string({$prefix}InstrId)", $node);
        $endToEndId = $xpath->evaluate("string({$prefix}EndToEndId)", $node);
        $uetr = $xpath->evaluate("string({$prefix}UETR)", $node);

        return new PaymentIdentification(
            endToEndId: !empty($endToEndId) ? $endToEndId : 'NOTPROVIDED',
            instructionId: !empty($instrId) ? $instrId : null,
            uetr: !empty($uetr) ? $uetr : null
        );
    }

    /**
     * Parst RemittanceInformation.
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param DOMNode|null $node Der RemittanceInformation-Node
     * @param string $prefix Namespace prefix
     * @return RemittanceInformation|null Die geparste Verwendungszweck-Info
     */
    protected static function parseRemittance(DOMXPath $xpath, ?DOMNode $node, string $prefix): ?RemittanceInformation {
        if (!$node) {
            return null;
        }

        // Unstructured (kann mehrere Elemente haben)
        $unstructuredLines = [];
        $ustrdNodes = $xpath->query("{$prefix}Ustrd", $node);
        foreach ($ustrdNodes as $ustrdNode) {
            $unstructuredLines[] = $ustrdNode->textContent;
        }

        // Structured Creditor Reference
        $creditorReference = $xpath->evaluate("string({$prefix}Strd/{$prefix}CdtrRefInf/{$prefix}Ref)", $node);

        // Only return if content is present
        if (empty($unstructuredLines) && empty($creditorReference)) {
            return null;
        }

        return new RemittanceInformation(
            unstructured: $unstructuredLines,
            creditorReference: !empty($creditorReference) ? $creditorReference : null
        );
    }

    // =========================================================================
    // HILFSMETHODEN FÜR PARTY-INFO (CAMT-STIL)
    // =========================================================================

    /**
     * Parst die Party-Informationen (Debtor/Creditor) aus CAMT-Struktur.
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param DOMNode $context Kontext-Node
     * @param string $partyType Der Party-Typ (Dbtr, Cdtr, etc.)
     * @param string $prefix Namespace prefix
     * @return array{name: ?string, iban: ?string, bic: ?string}
     */
    protected static function parsePartyInfo(DOMXPath $xpath, DOMNode $context, string $partyType, string $prefix): array {
        $name = static::xpathStringWithFallbackStatic($xpath, [
            "{$prefix}RltdPties/{$prefix}{$partyType}/{$prefix}Nm",
            "{$prefix}{$partyType}/{$prefix}Nm"
        ], $context);

        $iban = static::xpathStringWithFallbackStatic($xpath, [
            "{$prefix}RltdPties/{$prefix}{$partyType}Acct/{$prefix}Id/{$prefix}IBAN",
            "{$prefix}{$partyType}Acct/{$prefix}Id/{$prefix}IBAN"
        ], $context);

        $bic = static::xpathStringWithFallbackStatic($xpath, [
            "{$prefix}RltdAgts/{$prefix}{$partyType}Agt/{$prefix}FinInstnId/{$prefix}BICFI",
            "{$prefix}RltdAgts/{$prefix}{$partyType}Agt/{$prefix}FinInstnId/{$prefix}BIC",
            "{$prefix}{$partyType}Agt/{$prefix}FinInstnId/{$prefix}BICFI"
        ], $context);

        return ['name' => $name, 'iban' => $iban, 'bic' => $bic];
    }

    /**
     * Parst Remittance-Information aus verschiedenen Pfaden.
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param DOMNode $context Kontext-Node
     * @param string $prefix Namespace prefix
     * @return string|null Die Verwendungszweck-Information
     */
    protected static function parseRemittanceInfoString(DOMXPath $xpath, DOMNode $context, string $prefix): ?string {
        // Unstrukturierte Remittance-Info
        $ustrd = static::xpathStringStatic($xpath, "{$prefix}RmtInf/{$prefix}Ustrd", $context);
        if ($ustrd !== null) {
            return $ustrd;
        }

        // Strukturierte Remittance-Info - verschiedene Varianten
        return static::xpathStringWithFallbackStatic($xpath, [
            "{$prefix}RmtInf/{$prefix}Strd/{$prefix}CdtrRefInf/{$prefix}Ref",
            "{$prefix}RmtInf/{$prefix}Strd/{$prefix}RfrdDocInf/{$prefix}Nb",
            "{$prefix}RmtInf/{$prefix}Strd/{$prefix}AddtlRmtInf"
        ], $context);
    }

    /**
     * Parst Bank-Transaction-Code-Informationen.
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param DOMNode $context Kontext-Node
     * @param string $prefix Namespace prefix
     * @return array{code: ?string, domain: ?string, family: ?string, subFamily: ?string}
     */
    protected static function parseBankTxCode(DOMXPath $xpath, DOMNode $context, string $prefix): array {
        return [
            'code' => static::xpathStringWithFallbackStatic($xpath, [
                "{$prefix}BkTxCd/{$prefix}Prtry/{$prefix}Cd",
                "{$prefix}BkTxCd/{$prefix}Domn/{$prefix}Fmly/{$prefix}Cd"
            ], $context),
            'domain' => static::xpathStringStatic($xpath, "{$prefix}BkTxCd/{$prefix}Domn/{$prefix}Cd", $context),
            'family' => static::xpathStringStatic($xpath, "{$prefix}BkTxCd/{$prefix}Domn/{$prefix}Fmly/{$prefix}Cd", $context),
            'subFamily' => static::xpathStringStatic($xpath, "{$prefix}BkTxCd/{$prefix}Domn/{$prefix}Fmly/{$prefix}SubFmlyCd", $context)
        ];
    }

    // =========================================================================
    // STATISCHE XPATH-HILFSMETHODEN
    // =========================================================================

    /**
     * Evaluates an XPath expression and returns a string or null (static).
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param string $expression XPath-Ausdruck
     * @param DOMNode|null $context Kontext-Node (optional)
     * @return string|null Ergebnis oder null wenn leer
     */
    protected static function xpathStringStatic(DOMXPath $xpath, string $expression, ?DOMNode $context = null): ?string {
        if (!str_starts_with($expression, 'string(')) {
            $expression = "string({$expression})";
        }

        $result = $context !== null
            ? $xpath->evaluate($expression, $context)
            : $xpath->evaluate($expression);

        return !empty($result) ? (string)$result : null;
    }

    /**
     * Evaluates XPath expressions with fallback alternatives (static).
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param array<string> $expressions List of XPath expressions
     * @param DOMNode|null $context Kontext-Node (optional)
     * @return string|null Erstes nicht-leeres Ergebnis oder null
     */
    protected static function xpathStringWithFallbackStatic(DOMXPath $xpath, array $expressions, ?DOMNode $context = null): ?string {
        foreach ($expressions as $expression) {
            $result = static::xpathStringStatic($xpath, $expression, $context);
            if ($result !== null) {
                return $result;
            }
        }
        return null;
    }

    /**
     * Parst einen Betrag aus einem String (statisch).
     * 
     * @param string|null $amountStr Betrags-String
     * @return float Betrag als Float
     */
    protected static function parseAmountValue(?string $amountStr): float {
        if ($amountStr === null || $amountStr === '') {
            return 0.0;
        }
        return (float) str_replace(',', '.', $amountStr);
    }

    /**
     * Parses an amount with currency from an element with Ccy attribute (static).
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param string $amountPath XPath zum Betrags-Element
     * @param DOMNode $context Kontext-Node
     * @param CurrencyCode $default Default currency
     * @return array{amount: float, currency: CurrencyCode}
     */
    protected static function parseAmountWithCcy(
        DOMXPath $xpath,
        string $amountPath,
        DOMNode $context,
        CurrencyCode $default = CurrencyCode::Euro
    ): array {
        $amtNode = $xpath->query($amountPath, $context)->item(0);

        $amount = 0.0;
        $currency = $default;

        if ($amtNode instanceof DOMElement) {
            $amount = static::parseAmountValue($amtNode->textContent);
            $currencyStr = $amtNode->getAttribute('Ccy') ?: 'EUR';
            $currency = CurrencyCode::tryFrom($currencyStr) ?? $default;
        }

        return ['amount' => $amount, 'currency' => $currency];
    }

    /**
     * Converts empty strings to null (static).
     * 
     * @param string $value Der String
     * @return string|null Null wenn leer, sonst der String
     */
    protected static function emptyStringToNull(string $value): ?string {
        return $value !== '' ? $value : null;
    }

    // =========================================================================
    // ALIAS-METHODEN FÜR ABWÄRTSKOMPATIBILITÄT (PAIN)
    // =========================================================================

    /**
     * Alias for createIso20022Document - Pain compatibility.
     * 
     * @deprecated Verwende createIso20022Document
     */
    protected static function createDomXPath(string $xmlContent, string $painType, array $knownNamespaces = []): array {
        $result = static::createIso20022Document($xmlContent, $painType, $knownNamespaces);
        // Pain erwartet 'dom', 'xpath', 'prefix' - namespace nicht enthalten im alten Interface
        return [
            'dom' => $result['doc'],
            'xpath' => $result['doc']->getXPath(),
            'prefix' => $result['prefix']
        ];
    }

    /**
     * Alias for detectIso20022Namespace - Pain compatibility.
     * 
     * @deprecated Verwende detectIso20022Namespace
     */
    protected static function detectPainNamespace(DOMDocument $dom, string $painType, array $knownNamespaces = []): ?string {
        return static::detectIso20022Namespace($dom, $painType, $knownNamespaces);
    }

    /**
     * Alias for parseParty - Pain compatibility.
     * 
     * @deprecated Verwende parseParty
     */
    protected static function parsePartyIdentification(DOMXPath $xpath, ?DOMNode $node, string $prefix): PartyIdentification {
        return static::parseParty($xpath, $node, $prefix);
    }

    /**
     * Alias for parseAccount - Pain compatibility.
     * 
     * @deprecated Verwende parseAccount
     */
    protected static function parseAccountIdentification(DOMXPath $xpath, ?DOMNode $node, string $prefix): AccountIdentification {
        return static::parseAccount($xpath, $node, $prefix);
    }

    /**
     * Alias for parseFinancialInst - Pain compatibility.
     * 
     * @deprecated Verwende parseFinancialInst
     */
    protected static function parseFinancialInstitution(DOMXPath $xpath, ?DOMNode $node, string $prefix): FinancialInstitution {
        return static::parseFinancialInst($xpath, $node, $prefix);
    }

    /**
     * Alias for parsePaymentId - Pain compatibility.
     * 
     * @deprecated Verwende parsePaymentId
     */
    protected static function parsePaymentIdentification(DOMXPath $xpath, DOMNode $node, string $prefix): PaymentIdentification {
        return static::parsePaymentId($xpath, $node, $prefix);
    }

    /**
     * Alias for parseRemittance - Pain compatibility.
     * 
     * @deprecated Verwende parseRemittance
     */
    protected static function parseRemittanceInformation(DOMXPath $xpath, ?DOMNode $node, string $prefix): ?RemittanceInformation {
        return static::parseRemittance($xpath, $node, $prefix);
    }

    /**
     * Alias for parseAmountWithCcy - Pain compatibility (static version).
     * 
     * @deprecated Verwende parseAmountWithCcy
     */
    protected static function parseAmountWithCurrencyStatic(
        DOMXPath $xpath,
        string $amountPath,
        DOMNode $context,
        CurrencyCode $default = CurrencyCode::Euro
    ): array {
        return static::parseAmountWithCcy($xpath, $amountPath, $context, $default);
    }

    /**
     * Parst einen DateTime-String zu DateTimeImmutable (statisch).
     * 
     * @param string|null $dateTimeStr Der DateTime-String
     * @return \DateTimeImmutable|null
     */
    protected static function parseDateTimeStatic(?string $dateTimeStr): ?\DateTimeImmutable {
        if (empty($dateTimeStr)) {
            return null;
        }
        return new \DateTimeImmutable($dateTimeStr);
    }

    /**
     * Alias for parsePostalAddr - Pain compatibility.
     * 
     * @deprecated Verwende parsePostalAddr
     */
    protected static function parsePostalAddress(DOMXPath $xpath, DOMNode $node, string $prefix): PostalAddress {
        return static::parsePostalAddr($xpath, $node, $prefix);
    }
}
