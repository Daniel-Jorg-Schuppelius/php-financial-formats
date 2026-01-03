<?php

/*
 * Created on   : Tue Dec 31 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CamtReflectionParser.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Parsers;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\Parsers\ExtendedDOMDocumentParser;
use DateTimeImmutable;
use DOMDocument;
use DOMNode;
use DOMXPath;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;

/**
 * Reflection-basierter CAMT-Parser.
 * 
 * Nutzt PHP Reflection und XPath-Mappings, um CAMT-Dokumente
 * automatisch auf Entity-Klassen zu mappen.
 * 
 * @package CommonToolkit\FinancialFormats\Parsers
 */
class CamtReflectionParser {
    /**
     * XPath mapping configuration for CAMT types.
     * 
     * Format: [
     *   CamtType::CAMT031 => [
     *     'class' => Camt031Document::class,
     *     'root' => 'RjctInvstgtn',
     *     'mappings' => [
     *       'assignmentId' => 'Assgnmt/Id',
     *       'creationDateTime' => 'Assgnmt/CreDtTm',
     *       // ... weitere Mappings
     *     ],
     *     'postProcessor' => callable($document, $xpath, $rootNode, $prefix) // Optional
     *   ]
     * ]
     * 
     * @var array<string, array{class: class-string, root: string, mappings: array<string, string|array>, postProcessor?: callable}>
     */
    private static array $typeConfigs = [];

    /**
     * Common assignment paths (for many investigation documents).
     */
    private const ASSIGNMENT_MAPPINGS = [
        'assignmentId' => 'Assgnmt/Id',
        'creationDateTime' => 'Assgnmt/CreDtTm',
        'assignerAgentBic' => 'Assgnmt/Assgnr/Agt/FinInstnId/BICFI',
        'assignerPartyName' => 'Assgnmt/Assgnr/Pty/Nm',
        'assigneeAgentBic' => 'Assgnmt/Assgne/Agt/FinInstnId/BICFI',
        'assigneePartyName' => 'Assgnmt/Assgne/Pty/Nm',
        'caseId' => 'Case/Id',
        'caseCreator' => 'Case/Cretr/Pty/Nm',
    ];

    /**
     * Gemeinsame Underlying-Transaction-Pfade.
     */
    private const UNDERLYING_MAPPINGS = [
        'originalMessageId' => 'Undrlyg/Initn/OrgnlGrpInf/OrgnlMsgId',
        'originalMessageNameId' => 'Undrlyg/Initn/OrgnlGrpInf/OrgnlMsgNmId',
        'originalCreationDateTime' => 'Undrlyg/Initn/OrgnlGrpInf/OrgnlCreDtTm',
        'originalEndToEndId' => 'Undrlyg/Initn/OrgnlEndToEndId',
        'originalTransactionId' => 'Undrlyg/Initn/OrgnlTxId',
        'originalInterbankSettlementAmount' => 'Undrlyg/Initn/OrgnlIntrBkSttlmAmt',
        'originalCurrency' => 'Undrlyg/Initn/OrgnlIntrBkSttlmAmt/@Ccy',
        'originalInterbankSettlementDate' => 'Undrlyg/Initn/OrgnlIntrBkSttlmDt',
    ];

    /**
     * Registriert eine Typ-Konfiguration.
     * 
     * @param CamtType $type CAMT-Typ
     * @param class-string $class Entity-Klasse
     * @param string $root Root-Element-Name
     * @param array<string, string|array> $mappings XPath-Mappings (Parameter => XPath)
     * @param bool $includeAssignment Include common assignment mappings
     * @param bool $includeUnderlying Include common underlying mappings
     * @param callable|null $postProcessor Post-Processing-Callback: fn($document, DOMXPath, DOMNode, string $prefix)
     */
    public static function registerType(CamtType $type, string $class, string $root, array $mappings = [], bool $includeAssignment = false, bool $includeUnderlying = false, ?callable $postProcessor = null): void {
        $allMappings = [];

        if ($includeAssignment) {
            $allMappings = array_merge($allMappings, self::ASSIGNMENT_MAPPINGS);
        }

        if ($includeUnderlying) {
            $allMappings = array_merge($allMappings, self::UNDERLYING_MAPPINGS);
        }

        $allMappings = array_merge($allMappings, $mappings);

        self::$typeConfigs[$type->value] = [
            'class' => $class,
            'root' => $root,
            'mappings' => $allMappings,
            'postProcessor' => $postProcessor,
        ];
    }

    /**
     * Parst ein CAMT-Dokument basierend auf der registrierten Konfiguration.
     * 
     * @param string $xmlContent XML-Inhalt
     * @param CamtType $type CAMT-Typ
     * @return CamtDocumentInterface
     * @throws RuntimeException On missing mapping or invalid XML
     */
    public static function parse(string $xmlContent, CamtType $type): CamtDocumentInterface {
        if (!isset(self::$typeConfigs[$type->value])) {
            throw new RuntimeException("Keine Konfiguration für {$type->value} registriert.");
        }

        $config = self::$typeConfigs[$type->value];
        ['xpath' => $xpath, 'prefix' => $prefix] = self::initXPath($xmlContent);

        $rootNode = $xpath->query("//{$prefix}{$config['root']}")->item(0);
        if (!$rootNode) {
            throw new RuntimeException("Kein <{$config['root']}>-Block gefunden.");
        }

        $document = self::instantiateFromMappings(
            $config['class'],
            $xpath,
            $rootNode,
            $config['mappings'],
            $prefix
        );

        // Post-Processing für verschachtelte Strukturen
        if (isset($config['postProcessor']) && is_callable($config['postProcessor'])) {
            ($config['postProcessor'])($document, $xpath, $rootNode, $prefix);
        }

        return $document;
    }

    /**
     * Instanziiert eine Entity-Klasse basierend auf Reflection und XPath-Mappings.
     * 
     * @param class-string $className Entity-Klasse
     * @param DOMXPath $xpath XPath-Objekt
     * @param DOMNode $contextNode Kontext-Node
     * @param array<string, string|array> $mappings XPath-Mappings
     * @param string $prefix Namespace-Prefix
     * @return object Instanziierte Entity
     */
    private static function instantiateFromMappings(string $className, DOMXPath $xpath, DOMNode $contextNode, array $mappings, string $prefix): object {
        $reflection = new ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();

            if (!isset($mappings[$paramName])) {
                // Nutze Default-Wert oder null
                $args[$paramName] = $param->isDefaultValueAvailable()
                    ? $param->getDefaultValue()
                    : null;
                continue;
            }

            $mapping = $mappings[$paramName];
            $rawValue = self::extractValue($xpath, $contextNode, $mapping, $prefix);
            $args[$paramName] = self::convertValue($rawValue, $param);
        }

        // PHP 8+ Named Arguments über Spread-Operator
        return $reflection->newInstanceArgs($args);
    }

    /**
     * Extrahiert einen Wert aus dem XML basierend auf dem Mapping.
     * 
     * @param DOMXPath $xpath XPath-Objekt
     * @param DOMNode $contextNode Kontext-Node
     * @param string|array $mapping XPath oder Array von XPaths (Fallback)
     * @param string $prefix Namespace-Prefix
     * @return string|null Extrahierter Wert
     */
    private static function extractValue(DOMXPath $xpath, DOMNode $contextNode, string|array $mapping, string $prefix): ?string {
        // Array = Fallback-Pfade
        if (is_array($mapping)) {
            foreach ($mapping as $path) {
                $prefixedPath = self::prefixPath($path, $prefix);
                $result = $xpath->evaluate("string({$prefixedPath})", $contextNode);
                if (!empty($result)) {
                    return $result;
                }
            }
            return null;
        }

        // Einzelner Pfad
        $prefixedPath = self::prefixPath($mapping, $prefix);
        $result = $xpath->evaluate("string({$prefixedPath})", $contextNode);
        return empty($result) ? null : $result;
    }

    /**
     * Konvertiert einen extrahierten String-Wert in den erwarteten Typ.
     * 
     * @param string|null $value Rohwert
     * @param ReflectionParameter $param Konstruktor-Parameter
     * @return mixed Konvertierter Wert
     */
    private static function convertValue(?string $value, ReflectionParameter $param): mixed {
        $type = $param->getType();

        // Kein Typ definiert oder null-Wert
        if ($type === null || $value === null) {
            if ($value === null && $param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }
            return $value;
        }

        if (!$type instanceof ReflectionNamedType) {
            return $value;
        }

        $typeName = $type->getName();

        // Typ-Konvertierungen
        return match ($typeName) {
            'int' => (int) $value,
            'float' => (float) str_replace(',', '.', $value),
            'bool' => in_array(strtolower($value), ['true', '1', 'yes'], true),
            'string' => $value,
            DateTimeImmutable::class => new DateTimeImmutable($value),
            CurrencyCode::class => CurrencyCode::tryFrom(strtoupper($value)),
            CreditDebit::class => self::parseCreditDebit($value),
            default => $value,
        };
    }

    /**
     * Adds namespace prefix to an XPath.
     * 
     * @param string $path XPath ohne Prefix (z.B. "Assgnmt/Id")
     * @param string $prefix Namespace-Prefix (z.B. "ns:")
     * @return string XPath mit Prefix (z.B. "ns:Assgnmt/ns:Id")
     */
    private static function prefixPath(string $path, string $prefix): string {
        if (empty($prefix)) {
            return $path;
        }

        // Behandle Attribut-Pfade (@Attr)
        $parts = explode('/', $path);
        $prefixedParts = array_map(function ($part) use ($prefix) {
            // Attribute nicht prefixen
            if (str_starts_with($part, '@')) {
                return $part;
            }
            return $prefix . $part;
        }, $parts);

        return implode('/', $prefixedParts);
    }

    /**
     * Parst CreditDebit-Indicator.
     */
    private static function parseCreditDebit(string $value, bool $isReversal = false): CreditDebit {
        $creditDebit = match (strtoupper($value)) {
            'CRDT' => CreditDebit::CREDIT,
            'DBIT' => CreditDebit::DEBIT,
            default => CreditDebit::DEBIT,
        };

        // Storno kehrt die Richtung um
        if ($isReversal) {
            return $creditDebit === CreditDebit::CREDIT
                ? CreditDebit::DEBIT
                : CreditDebit::CREDIT;
        }

        return $creditDebit;
    }

    /**
     * Initializes DOM and XPath for an XML document.
     */
    private static function initXPath(string $xmlContent): array {
        $doc = ExtendedDOMDocumentParser::fromString($xmlContent);
        $namespace = self::detectNamespace($doc);
        $useNamespace = !empty($namespace);

        if ($useNamespace) {
            $doc->registerXPathNamespace('ns', $namespace);
        }

        return [
            'dom' => $doc,
            'xpath' => $doc->getXPath(),
            'namespace' => $namespace,
            'prefix' => $useNamespace ? 'ns:' : ''
        ];
    }

    /**
     * Erkennt den Namespace im Dokument.
     */
    private static function detectNamespace(DOMDocument $dom): string {
        $root = $dom->documentElement;
        if (!$root) {
            return '';
        }

        $ns = $root->namespaceURI ?? $root->getAttribute('xmlns');
        if (!empty($ns)) {
            return $ns;
        }

        foreach ($root->attributes as $attr) {
            if (str_starts_with($attr->name, 'xmlns') && str_contains($attr->value, 'camt.')) {
                return $attr->value;
            }
        }

        return '';
    }

    /**
     * Returns all registered type configurations.
     * 
     * @return array<string, array>
     */
    public static function getRegisteredTypes(): array {
        return self::$typeConfigs;
    }

    /**
     * Clears all registered type configurations.
     */
    public static function clearRegistrations(): void {
        self::$typeConfigs = [];
    }
}
