<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CamtVersion.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Camt;

/**
 * CAMT message versions according to ISO 20022.
 * 
 * Die Versionsnummern entsprechen dem Schema camt.0XX.001.VV
 * where VV is the version number listed here.
 * 
 * @package CommonToolkit\Enums\Common\Banking
 */
enum CamtVersion: string {
    /**
     * Version 02 - Initiale weit verbreitete Version
     */
    case V02 = '02';

    /**
     * Version 04 - Extensions for SEPA
     */
    case V04 = '04';

    /**
     * Version 05 - Zwischenversion (CAMT.038)
     */
    case V05 = '05';

    /**
     * Version 06 - Zwischenversion (CAMT.052)
     */
    case V06 = '06';

    /**
     * Version 07 - Zwischenversion (CAMT.031, CAMT.033, CAMT.034)
     */
    case V07 = '07';

    /**
     * Version 08 - Aktuelle Standardversion
     */
    case V08 = '08';

    /**
     * Version 09 - Zwischenversion (CAMT.058, CAMT.087)
     */
    case V09 = '09';

    /**
     * Version 10 - Neuere Erweiterungen
     */
    case V10 = '10';

    /**
     * Version 11 - Zwischenversion (CAMT.056)
     */
    case V11 = '11';

    /**
     * Version 12 - Aktuellste Version
     */
    case V12 = '12';

    /**
     * Version 13 - Neueste Version (2024/2025)
     */
    case V13 = '13';

    /**
     * Returns the complete namespace for a CAMT type.
     */
    public function getNamespace(CamtType $type): string {
        return "urn:iso:std:iso:20022:tech:xsd:{$type->value}.001.{$this->value}";
    }

    /**
     * Returns the version number as integer.
     */
    public function toInt(): int {
        return (int) $this->value;
    }

    /**
     * Erstellt eine Version aus einem Integer.
     */
    public static function fromInt(int $version): ?self {
        $padded = str_pad((string) $version, 2, '0', STR_PAD_LEFT);
        return self::tryFrom($padded);
    }

    /**
     * Creates a version from a string (with or without leading zero).
     */
    public static function fromString(string $version): ?self {
        $padded = str_pad(ltrim($version, '0') ?: '0', 2, '0', STR_PAD_LEFT);
        return self::tryFrom($padded);
    }

    /**
     * Returns the description of the version.
     */
    public function getDescription(): string {
        return match ($this) {
            self::V02 => 'ISO 20022 Version 02 (Initial)',
            self::V04 => 'ISO 20022 Version 04 (SEPA)',
            self::V05 => 'ISO 20022 Version 05',
            self::V06 => 'ISO 20022 Version 06',
            self::V07 => 'ISO 20022 Version 07',
            self::V08 => 'ISO 20022 Version 08 (Standard)',
            self::V09 => 'ISO 20022 Version 09',
            self::V10 => 'ISO 20022 Version 10',
            self::V11 => 'ISO 20022 Version 11',
            self::V12 => 'ISO 20022 Version 12',
            self::V13 => 'ISO 20022 Version 13 (Aktuell)',
        };
    }

    /**
     * Checks if this version is newer than another.
     */
    public function isNewerThan(self $other): bool {
        return $this->toInt() > $other->toInt();
    }

    /**
     * Checks if this version is older than another.
     */
    public function isOlderThan(self $other): bool {
        return $this->toInt() < $other->toInt();
    }

    /**
     * Returns the default version (V02 for maximum compatibility).
     */
    public static function default(): self {
        return self::V02;
    }
}
