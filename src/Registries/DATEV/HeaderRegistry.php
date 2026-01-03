<?php
/*
 * Created on   : Mon Dec 08 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : HeaderRegistry.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Registries\DATEV;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\DATEV\{FieldHeaderInterface, MetaHeaderDefinitionInterface};
use CommonToolkit\Entities\CSV\DataLine;
use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\Category;
use RuntimeException;

/**
 * Central registry for DATEV header definitions.
 * Uses the VersionDiscovery system for automatic detection of available versions.
 * 
 * The format definitions are now directly available via HeaderField enums,
 * die FieldHeaderInterface implementieren.
 * 
 * @see VersionDiscovery For automatic version detection
 */
final class HeaderRegistry {
    /** @var array<int, MetaHeaderDefinitionInterface> */
    private static array $metaHeaderInstances = [];


    /**
     * Returns the MetaHeader definition for a version (dynamically via Discovery).
     */
    public static function get(int $version): MetaHeaderDefinitionInterface {
        // Prüfe zuerst ob durch Discovery verfügbar
        if (!VersionDiscovery::isVersionSupported($version)) {
            throw new RuntimeException("DATEV-Version {$version} wird nicht unterstützt oder wurde nicht gefunden.");
        }

        // Singleton-Pattern für Instanzen
        if (!isset(self::$metaHeaderInstances[$version])) {
            $class = VersionDiscovery::getMetaHeaderClass($version);
            if (!$class) {
                throw new RuntimeException("Keine MetaHeader-Definition für Version {$version} gefunden.");
            }
            self::$metaHeaderInstances[$version] = new $class();
        }

        return self::$metaHeaderInstances[$version];
    }

    /**
     * Returns the format enum for a category and version.
     * 
     * @return class-string<FieldHeaderInterface>
     */
    public static function getFormatEnum(Category $category, int $version): string {
        if (!VersionDiscovery::isFormatSupported($category, $version)) {
            throw new RuntimeException(
                "Format '{$category->nameValue()}' wird in Version {$version} nicht unterstützt."
            );
        }

        $enumClass = VersionDiscovery::getFormatEnum($category, $version);
        if (!$enumClass) {
            throw new RuntimeException(
                "Kein Format-Enum für '{$category->nameValue()}' Version {$version} gefunden."
            );
        }

        return $enumClass;
    }

    /**
     * Alias for getFormatEnum for backwards compatibility.
     * @deprecated Use getFormatEnum() instead
     * 
     * @return class-string<FieldHeaderInterface>
     */
    public static function getFormatDefinition(Category $category, int $version): string {
        return self::getFormatEnum($category, $version);
    }

    /**
     * Checks if a format/version combination is supported (dynamically).
     */
    public static function isFormatSupported(Category $category, int $version): bool {
        return VersionDiscovery::isFormatSupported($category, $version);
    }

    /**
     * Automatische Erkennung aus dem rohen Werte-Array.
     * Checks the version number at position 1 (fixed DATEV structure).
     */
    public static function detectFromValues(array $values): ?MetaHeaderDefinitionInterface {
        // Versionsnummer muss an Position 1 stehen (DATEV-Standard)
        if (isset($values[1]) && preg_match('/^\d+$/', (string)$values[1])) {
            $version = (int)$values[1];
            if (VersionDiscovery::isVersionSupported($version)) {
                return self::get($version);
            }
        }

        return null;
    }

    /**
     * Automatische Erkennung direkt aus einer geparsten DataLine.
     * Checks the version number at position 1 (fixed DATEV structure).
     */
    public static function detectFromDataLine(DataLine $dataLine): ?MetaHeaderDefinitionInterface {
        $fields = $dataLine->getFields();

        // Versionsnummer an Position 1 prüfen (DATEV-Standard)
        if (isset($fields[1])) {
            $versionValue = $fields[1]->getValue();
            if (preg_match('/^\d+$/', $versionValue)) {
                $version = (int)$versionValue;

                // Direct fallback for version 700 if discovery fails
                if ($version === 700) {
                    try {
                        return self::get($version);
                    } catch (\Exception $e) {
                        // If discovery fails, still allow version 700
                        return null;
                    }
                }

                if (VersionDiscovery::isVersionSupported($version)) {
                    return self::get($version);
                }
            }
        }

        return null;
    }

    /**
     * Returns all supported versions (dynamically via Discovery).
     * 
     * @return int[]
     */
    public static function getSupportedVersions(): array {
        return VersionDiscovery::getSupportedVersions();
    }

    /**
     * Returns all available versions (including unsupported ones, dynamically).
     * 
     * @return int[]
     */
    public static function getAvailableVersions(): array {
        return VersionDiscovery::getAvailableVersions();
    }

    /**
     * Returns all supported formats for a version (dynamisch).
     * 
     * @return Category[]
     */
    public static function getSupportedFormats(int $version): array {
        return VersionDiscovery::getSupportedFormats($version);
    }

    /**
     * Returns all supported format-version combinations (dynamically).
     * 
     * @return array<string, array{category: Category, version: int, supported: bool}>
     */
    public static function getAllFormatVersionCombinations(): array {
        $combinations = [];
        $availableVersions = VersionDiscovery::getAvailableVersions();

        foreach ($availableVersions as $version) {
            foreach (Category::cases() as $category) {
                $key = "{$category->nameValue()}_v{$version}";
                $combinations[$key] = [
                    'category' => $category,
                    'version' => $version,
                    'supported' => VersionDiscovery::isFormatSupported($category, $version),
                ];
            }
        }

        return $combinations;
    }

    /**
     * Returns detailed information about all detected versions.
     * 
     * @return array<int, array{version: int, path: string, metaHeaderClass: ?string, formatEnums: array<int, class-string<FieldHeaderInterface>>, formatCount: int}>
     */
    public static function getVersionDetails(): array {
        return VersionDiscovery::getVersionDetails();
    }

    /**
     * Checks the consistency of a version.
     * 
     * @return array{valid: bool, missing: string[], issues: string[]}
     */
    public static function validateVersion(int $version): array {
        return VersionDiscovery::validateVersion($version);
    }

    /**
     * Forces re-detection of available versions.
     */
    public static function refresh(): void {
        VersionDiscovery::refresh();
        self::clearCache();
    }

    /**
     * Clears the instance cache (for tests).
     */
    public static function clearCache(): void {
        self::$metaHeaderInstances = [];
    }
}
