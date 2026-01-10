<?php
/*
 * Created on   : Fri Jan 10 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : ValidatorAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022;

use CommonToolkit\Contracts\Abstracts\XML\XsdValidatorAbstract;
use CommonToolkit\Entities\XML\XsdValidationResult;
use UnitEnum;

/**
 * Abstract base class for ISO 20022 XSD validators.
 * 
 * Provides common functionality for CAMT, Pain, and other ISO 20022 format validators
 * with dynamic XSD file discovery from the filesystem.
 * 
 * @package CommonToolkit\FinancialFormats\Contracts\Abstracts
 */
abstract class ValidatorAbstract extends XsdValidatorAbstract {
    /**
     * Cache for discovered XSD files per class
     * @var array<string, array<string, array<string, string>>>
     */
    private static array $discoveredXsdFilesCache = [];

    /**
     * Returns the message type prefix (e.g., 'camt', 'pain', 'pacs').
     */
    abstract protected static function getMessageTypePrefix(): string;

    /**
     * Returns the UnitEnum type from XML content.
     * 
     * @return UnitEnum|null The detected type enum or null
     */
    abstract public static function detectType(string $xmlContent): ?UnitEnum;

    /**
     * Tries to create a UnitEnum from a type key string.
     * 
     * @param string $typeKey The type key (e.g., 'camt.053', 'pain.001')
     * @return UnitEnum|null The enum or null if not found
     */
    abstract protected static function tryFromTypeKey(string $typeKey): ?UnitEnum;

    /**
     * Converts a version enum to string value.
     * 
     * @param mixed $version The version (enum or string)
     * @return string|null The version string
     */
    abstract protected static function versionToString(mixed $version): ?string;

    /**
     * {@inheritDoc}
     * 
     * Dynamically discovers XSD files from the filesystem.
     */
    protected static function getXsdFiles(): array {
        $cacheKey = static::getMessageTypePrefix();

        if (!isset(self::$discoveredXsdFilesCache[$cacheKey])) {
            self::$discoveredXsdFilesCache[$cacheKey] = static::discoverXsdFiles();
        }

        return self::$discoveredXsdFilesCache[$cacheKey];
    }

    /**
     * Discovers all available XSD files from the filesystem.
     * 
     * @return array<string, array<string, string>> Type => Version => Filename
     */
    protected static function discoverXsdFiles(): array {
        $xsdFiles = [];
        $basePath = static::getXsdBasePath();
        $prefix = static::getMessageTypePrefix();

        if (!is_dir($basePath)) {
            return $xsdFiles;
        }

        $files = glob($basePath . $prefix . '.*.xsd');
        if ($files === false) {
            return $xsdFiles;
        }

        // Pattern: {prefix}.XXX.001.YY.xsd (e.g., camt.053.001.02.xsd)
        $pattern = '/^' . preg_quote($prefix, '/') . '\.(\d{3})\.001\.(\d{2})\.xsd$/';

        foreach ($files as $file) {
            $filename = basename($file);

            if (preg_match($pattern, $filename, $matches)) {
                $typeNumber = $matches[1];
                $version = $matches[2];
                $typeKey = $prefix . '.' . $typeNumber;

                $xsdFiles[$typeKey][$version] = $filename;
            }
        }

        // Sort versions for each type (ascending)
        foreach ($xsdFiles as $type => $versions) {
            ksort($xsdFiles[$type]);
        }

        return $xsdFiles;
    }

    /**
     * Clears the XSD file cache (useful for testing).
     */
    public static function clearCache(): void {
        $cacheKey = static::getMessageTypePrefix();
        unset(self::$discoveredXsdFilesCache[$cacheKey]);
    }

    /**
     * Clears all XSD file caches.
     */
    public static function clearAllCaches(): void {
        self::$discoveredXsdFilesCache = [];
    }

    /**
     * Detects the type number from XML content.
     * 
     * @return string|null The type number (e.g., "053", "001")
     */
    public static function detectTypeNumber(string $xmlContent): ?string {
        $prefix = static::getMessageTypePrefix();

        // Namespace pattern: urn:iso:std:iso:20022:tech:xsd:{prefix}.XXX.001.YY
        $pattern = '/urn:iso:std:iso:20022:tech:xsd:' . preg_quote($prefix, '/') . '\.(\d{3})\.001\.\d{2}/';
        if (preg_match($pattern, $xmlContent, $matches)) {
            return $matches[1];
        }

        // Alternative namespace pattern for older documents
        $altPattern = '/xmlns[^=]*=\s*["\'][^"\']*' . preg_quote($prefix, '/') . '\.(\d{3})\.001\.\d{2}[^"\']*["\']/';
        if (preg_match($altPattern, $xmlContent, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public static function detectVersion(string $xmlContent): ?string {
        $prefix = static::getMessageTypePrefix();

        // Namespace pattern: urn:iso:std:iso:20022:tech:xsd:{prefix}.XXX.001.YY
        $pattern = '/urn:iso:std:iso:20022:tech:xsd:' . preg_quote($prefix, '/') . '\.\d{3}\.001\.(\d{2})/';
        if (preg_match($pattern, $xmlContent, $matches)) {
            return $matches[1];
        }

        // Alternative namespace pattern for older documents
        $altPattern = '/xmlns[^=]*=\s*["\'][^"\']*' . preg_quote($prefix, '/') . '\.(\d{3})\.001\.(\d{2})[^"\']*["\']/';
        if (preg_match($altPattern, $xmlContent, $matches)) {
            return $matches[2];
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    protected static function getTypeKey(UnitEnum $type): ?string {
        return $type->value ?? null;
    }

    /**
     * Validates an XML document against the corresponding XSD schema.
     * 
     * @param string $xmlContent The XML content
     * @param UnitEnum|null $type Optional: Document type (auto-detected if null)
     * @param string|null $version Optional: Document version (auto-detected if null)
     * @return XsdValidationResult The validation result
     */
    public static function validate(
        string $xmlContent,
        ?UnitEnum $type = null,
        ?string $version = null
    ): XsdValidationResult {
        $prefix = static::getMessageTypePrefix();

        // Auto-detect type if not provided
        if ($type === null) {
            $typeNumber = static::detectTypeNumber($xmlContent);
            if ($typeNumber === null) {
                return new XsdValidationResult(
                    valid: false,
                    errors: ['Unknown document type'],
                    type: null,
                    version: null
                );
            }

            // Try to get type enum
            $type = static::detectType($xmlContent);

            // If no type enum exists, use type key directly
            if ($type === null) {
                return static::validateWithTypeKey($xmlContent, $prefix . '.' . $typeNumber, $version);
            }
        }

        return parent::validate($xmlContent, $type, $version);
    }

    /**
     * Validates an XML with explicit type (enum or string).
     * 
     * @param string $xmlContent The XML content
     * @param UnitEnum|string $type Document type (enum or string like "camt.053")
     * @param mixed $version Optional: Document version (enum or string)
     * @return XsdValidationResult The validation result
     */
    public static function validateWithType(
        string $xmlContent,
        UnitEnum|string $type,
        mixed $version = null
    ): XsdValidationResult {
        // Convert version enum to string
        $version = static::versionToString($version);

        // If type is a string, validate directly with type key
        if (is_string($type)) {
            return static::validateWithTypeKey($xmlContent, $type, $version);
        }

        return parent::validate($xmlContent, $type, $version);
    }

    /**
     * Validates using a type key string directly.
     */
    protected static function validateWithTypeKey(string $xmlContent, string $typeKey, ?string $version): XsdValidationResult {
        // Auto-detect version
        if ($version === null) {
            $version = static::detectVersion($xmlContent);
        }

        // Get XSD file
        $xsdFiles = static::getXsdFiles();
        $xsdBasePath = static::getXsdBasePath();

        if (!isset($xsdFiles[$typeKey])) {
            return new XsdValidationResult(
                valid: false,
                errors: ["No XSD file found for {$typeKey}"],
                type: null,
                version: $version
            );
        }

        // Try exact version or fallback to latest
        $xsdFile = null;
        if ($version !== null && isset($xsdFiles[$typeKey][$version])) {
            $xsdFile = $xsdBasePath . $xsdFiles[$typeKey][$version];
        } else {
            // Use latest version
            $versions = array_keys($xsdFiles[$typeKey]);
            rsort($versions);
            foreach ($versions as $v) {
                $file = $xsdBasePath . $xsdFiles[$typeKey][$v];
                if (file_exists($file)) {
                    $xsdFile = $file;
                    break;
                }
            }
        }

        if ($xsdFile === null || !file_exists($xsdFile)) {
            return new XsdValidationResult(
                valid: false,
                errors: ["No XSD file found for {$typeKey} version " . ($version ?? 'unknown')],
                type: null,
                version: $version,
                xsdFile: $xsdFile
            );
        }

        // Use parent validation logic with type enum if available
        return parent::validate($xmlContent, static::tryFromTypeKey($typeKey), $version);
    }

    /**
     * Validates a file against the corresponding XSD schema.
     * 
     * @param string $filePath Path to the XML file
     * @param UnitEnum|null $type Optional: Document type
     * @param string|null $version Optional: Document version
     * @return XsdValidationResult The validation result
     */
    public static function validateFile(
        string $filePath,
        ?UnitEnum $type = null,
        ?string $version = null
    ): XsdValidationResult {
        return parent::validateFile($filePath, $type, $version);
    }

    /**
     * Validates a file with explicit type (enum or string).
     * 
     * @param string $filePath Path to the XML file
     * @param UnitEnum|string $type Document type (enum or string)
     * @param mixed $version Optional: Document version (enum or string)
     * @return XsdValidationResult The validation result
     */
    public static function validateFileWithType(
        string $filePath,
        UnitEnum|string $type,
        mixed $version = null
    ): XsdValidationResult {
        // Convert version enum to string
        $version = static::versionToString($version);

        // If type is a string, read file and validate with type key
        if (is_string($type)) {
            try {
                $xmlContent = file_get_contents($filePath);
                if ($xmlContent === false) {
                    return new XsdValidationResult(
                        valid: false,
                        errors: ["Cannot read file: {$filePath}"],
                        type: null,
                        version: $version
                    );
                }
                return static::validateWithTypeKey($xmlContent, $type, $version);
            } catch (\Throwable $e) {
                return new XsdValidationResult(
                    valid: false,
                    errors: [$e->getMessage()],
                    type: null,
                    version: $version
                );
            }
        }

        return parent::validateFile($filePath, $type, $version);
    }

    /**
     * Returns all discovered document types.
     * 
     * @return string[] Array of type keys (e.g., ['camt.003', 'camt.004', ...])
     */
    public static function getAvailableTypes(): array {
        return array_keys(static::getXsdFiles());
    }

    /**
     * Returns all available versions for a specific type.
     * 
     * @param string $typeKey Type key (e.g., 'camt.053')
     * @return string[] Array of versions (e.g., ['02', '04', '08', ...])
     */
    public static function getVersionsForType(string $typeKey): array {
        $xsdFiles = static::getXsdFiles();
        return isset($xsdFiles[$typeKey]) ? array_keys($xsdFiles[$typeKey]) : [];
    }
}
