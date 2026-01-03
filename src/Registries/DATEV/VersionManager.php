<?php
/*
 * Created on   : Mon Dec 21 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : VersionManager.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Registries\DATEV;

use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\Category;
use CommonToolkit\FinancialFormats\Entities\DATEV\DocumentInfo;

/**
 * Central management for DATEV versions and format information.
 * Provides high-level functions for querying versions and formats.
 * Nutzt das VersionDiscovery-System zur automatischen Erkennung.
 */
final class VersionManager {

    /**
     * Returns information about all available DATEV versions (dynamically).
     * 
     * @return array<int, array{version: int, supported: bool, formats: string[]}>
     */
    public static function getVersionOverview(): array {
        $overview = [];
        $availableVersions = VersionDiscovery::getAvailableVersions();

        foreach ($availableVersions as $version) {
            $supportedFormats = [];
            $isSupported = VersionDiscovery::isVersionSupported($version);

            if ($isSupported) {
                foreach (Category::cases() as $category) {
                    if (VersionDiscovery::isFormatSupported($category, $version)) {
                        $supportedFormats[] = $category->nameValue();
                    }
                }
            }

            $overview[$version] = [
                'version' => $version,
                'supported' => $isSupported,
                'formats' => $supportedFormats,
            ];
        }

        return $overview;
    }

    /**
     * Returns a matrix of all format-version combinations (dynamically).
     * 
     * @return array<string, array<string, bool>>
     */
    public static function getCompatibilityMatrix(): array {
        $matrix = [];
        $availableVersions = VersionDiscovery::getAvailableVersions();

        // Alle Kategorien als Zeilen
        foreach (Category::cases() as $category) {
            $formatName = $category->nameValue();
            $matrix[$formatName] = [];

            // Alle verfügbaren Versionen als Spalten
            foreach ($availableVersions as $version) {
                $versionKey = "v{$version}";
                $matrix[$formatName][$versionKey] = VersionDiscovery::isFormatSupported($category, $version);
            }
        }

        return $matrix;
    }

    /**
     * Determines the best available version for a format (dynamically).
     * 
     * @param Category $category The desired format
     * @return int|null The best available version or null
     */
    public static function getBestVersionForFormat(Category $category): ?int {
        // Sortiere Versionen absteigend (neueste zuerst)
        $supportedVersions = VersionDiscovery::getSupportedVersions();
        rsort($supportedVersions);

        foreach ($supportedVersions as $version) {
            if (VersionDiscovery::isFormatSupported($category, $version)) {
                return $version;
            }
        }

        return null;
    }

    /**
     * Checks if a format is available in a specific version (dynamically).
     */
    public static function isAvailable(Category $category, int $version): bool {
        return VersionDiscovery::isFormatSupported($category, $version);
    }

    /**
     * Creates DocumentInfo for a format and version.
     */
    public static function createDocumentInfo(Category $category, int $version): DocumentInfo {
        $definitionClass = null;

        if (VersionDiscovery::isVersionSupported($version) && VersionDiscovery::isFormatSupported($category, $version)) {
            $definitionClass = VersionDiscovery::getFormatDefinition($category, $version);
        }

        return new DocumentInfo($category, $version, $definitionClass);
    }

    /**
     * Returns all supported formats for the latest version (dynamically).
     * 
     * @return Category[]
     */
    public static function getCurrentSupportedFormats(): array {
        $supportedVersions = VersionDiscovery::getSupportedVersions();
        if (empty($supportedVersions)) {
            return [];
        }

        $latestVersion = max($supportedVersions);
        return VersionDiscovery::getSupportedFormats($latestVersion);
    }

    /**
     * Migration help: Shows which formats can be migrated from an old version to a new one (dynamically).
     * 
     * @param int $fromVersion Alte Version
     * @param int $toVersion Neue Version
     * @return array{migratable: Category[], not_migratable: Category[], new_formats: Category[]}
     */
    public static function getMigrationPlan(int $fromVersion, int $toVersion): array {
        $fromFormats = VersionDiscovery::getSupportedFormats($fromVersion);
        $toFormats = VersionDiscovery::getSupportedFormats($toVersion);

        $migratable = [];
        $notMigratable = [];
        $newFormats = [];

        // Prüfe, welche alten Formate in der neuen Version verfügbar sind
        foreach ($fromFormats as $category) {
            if (in_array($category, $toFormats, true)) {
                $migratable[] = $category;
            } else {
                $notMigratable[] = $category;
            }
        }

        // Prüfe auf neue Formate in der Zielversion
        foreach ($toFormats as $category) {
            if (!in_array($category, $fromFormats, true)) {
                $newFormats[] = $category;
            }
        }

        return [
            'migratable' => $migratable,
            'not_migratable' => $notMigratable,
            'new_formats' => $newFormats,
        ];
    }

    /**
     * Returns a human-readable overview of all versions (dynamically).
     */
    public static function getVersionSummary(): string {
        $overview = self::getVersionOverview();
        $lines = [];

        $lines[] = "DATEV Versionen-Übersicht (dynamisch erkannt):";
        $lines[] = str_repeat('=', 50);

        foreach ($overview as $info) {
            $status = $info['supported'] ? '✅ Unterstützt' : '❌ Nicht unterstützt';
            $formatCount = count($info['formats']);

            $lines[] = sprintf(
                "Version %d: %s (%d Formate)",
                $info['version'],
                $status,
                $formatCount
            );

            if ($formatCount > 0) {
                $lines[] = "  Formate: " . implode(', ', $info['formats']);
            }
            $lines[] = "";
        }

        // Zusätzliche Discovery-Informationen
        $versionDetails = VersionDiscovery::getVersionDetails();
        if (!empty($versionDetails)) {
            $lines[] = "Discovery Details:";
            $lines[] = str_repeat('-', 30);

            foreach ($versionDetails as $version => $details) {
                $lines[] = sprintf(
                    "V%d: %s (%d Klassen)",
                    $version,
                    $details['metaHeaderClass'] ? 'Gültig' : 'Unvollständig',
                    $details['formatCount'] + 1
                );
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Erzwingt eine Aktualisierung der Discovery-Erkennung.
     */
    public static function refresh(): void {
        VersionDiscovery::refresh();
    }

    /**
     * Returns detailed validation information for all versions.
     * 
     * @return array<int, array{valid: bool, missing: string[], issues: string[]}>
     */
    public static function validateAllVersions(): array {
        $results = [];
        $availableVersions = VersionDiscovery::getAvailableVersions();

        foreach ($availableVersions as $version) {
            $results[$version] = VersionDiscovery::validateVersion($version);
        }

        return $results;
    }
}
