<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PainVersion.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\ISO20022\Pain;

/**
 * Pain message versions according to ISO 20022.
 * 
 * Die Versionsnummern entsprechen dem Schema pain.0XX.001.VV
 * where VV is the version number listed here.
 * 
 * @package CommonToolkit\Enums\Common\Banking
 */
enum PainVersion: string {
    /**
     * Version 01 - Initial version
     */
    case V01 = '01';

    /**
     * Version 02 - Early version
     */
    case V02 = '02';

    /**
     * Version 03 - SEPA initial version
     */
    case V03 = '03';

    /**
     * Version 04 - Mandate extension (pain.017, pain.018)
     */
    case V04 = '04';

    /**
     * Version 05 - Intermediate version
     */
    case V05 = '05';

    /**
     * Version 06 - Intermediate version
     */
    case V06 = '06';

    /**
     * Version 07 - Intermediate version
     */
    case V07 = '07';

    /**
     * Version 08 - Mandate formats (pain.009-012)
     */
    case V08 = '08';

    /**
     * Version 09 - Intermediate version
     */
    case V09 = '09';

    /**
     * Version 10 - Creditor Payment Activation (pain.013, pain.014)
     */
    case V10 = '10';

    /**
     * Version 11 - Direct Debit & Creditor formats (pain.008, pain.013, pain.014)
     */
    case V11 = '11';

    /**
     * Version 12 - Current standard for Credit Transfer (pain.001, pain.007)
     */
    case V12 = '12';

    /**
     * Version 13 - Intermediate version
     */
    case V13 = '13';

    /**
     * Version 14 - Latest version for Payment Status (pain.002)
     */
    case V14 = '14';

    /**
     * Returns the complete namespace for a Pain type.
     */
    public function getNamespace(PainType $type): string {
        return "urn:iso:std:iso:20022:tech:xsd:{$type->value}.001.{$this->value}";
    }

    /**
     * Returns the schema location for a Pain type.
     */
    public function getSchemaLocation(PainType $type): string {
        return "{$type->value}.001.{$this->value}.xsd";
    }

    /**
     * Returns the short version string.
     */
    public function getShortVersion(): string {
        return $this->value;
    }

    /**
     * Parses version from namespace string.
     */
    public static function fromNamespace(string $namespace): ?self {
        // Extract version from namespace like: urn:iso:std:iso:20022:tech:xsd:pain.001.001.12
        if (preg_match('/pain\.\d{3}\.001\.(\d{2})/', $namespace, $matches)) {
            foreach (self::cases() as $case) {
                if ($case->value === $matches[1]) {
                    return $case;
                }
            }
        }
        return null;
    }

    /**
     * Get the default (latest recommended) version for a Pain type.
     */
    public static function getDefault(PainType $type): self {
        return match ($type) {
            PainType::PAIN_001 => self::V12,    // pain.001.001.12.xsd
            PainType::PAIN_002 => self::V14,    // pain.002.001.14.xsd
            PainType::PAIN_007 => self::V12,    // pain.007.001.12.xsd
            PainType::PAIN_008 => self::V11,    // pain.008.001.11.xsd
            PainType::PAIN_009 => self::V08,    // pain.009.001.08.xsd
            PainType::PAIN_010 => self::V08,    // pain.010.001.08.xsd
            PainType::PAIN_011 => self::V08,    // pain.011.001.08.xsd
            PainType::PAIN_012 => self::V08,    // pain.012.001.08.xsd
            PainType::PAIN_013 => self::V11,    // pain.013.001.11.xsd
            PainType::PAIN_014 => self::V11,    // pain.014.001.11.xsd
            PainType::PAIN_017 => self::V04,    // pain.017.001.04.xsd
            PainType::PAIN_018 => self::V04,    // pain.018.001.04.xsd
        };
    }

    /**
     * Returns supported versions for a Pain type (based on available XSDs and common usage).
     */
    public static function getSupportedVersions(PainType $type): array {
        return match ($type) {
            PainType::PAIN_001 => [self::V03, self::V08, self::V09, self::V10, self::V11, self::V12],
            PainType::PAIN_002 => [self::V03, self::V09, self::V10, self::V11, self::V12, self::V13, self::V14],
            PainType::PAIN_007 => [self::V02, self::V08, self::V09, self::V10, self::V11, self::V12],
            PainType::PAIN_008 => [self::V02, self::V08, self::V09, self::V10, self::V11],
            PainType::PAIN_009 => [self::V01, self::V02, self::V03, self::V04, self::V05, self::V06, self::V07, self::V08],
            PainType::PAIN_010 => [self::V01, self::V02, self::V03, self::V04, self::V05, self::V06, self::V07, self::V08],
            PainType::PAIN_011 => [self::V01, self::V02, self::V03, self::V04, self::V05, self::V06, self::V07, self::V08],
            PainType::PAIN_012 => [self::V01, self::V02, self::V03, self::V04, self::V05, self::V06, self::V07, self::V08],
            PainType::PAIN_013 => [self::V01, self::V02, self::V03, self::V04, self::V05, self::V06, self::V07, self::V08, self::V09, self::V10, self::V11],
            PainType::PAIN_014 => [self::V01, self::V02, self::V03, self::V04, self::V05, self::V06, self::V07, self::V08, self::V09, self::V10, self::V11],
            PainType::PAIN_017 => [self::V01, self::V02, self::V03, self::V04],
            PainType::PAIN_018 => [self::V01, self::V02, self::V03, self::V04],
        };
    }

    /**
     * Check if this version is supported for the given Pain type.
     */
    public function isSupported(PainType $type): bool {
        return in_array($this, self::getSupportedVersions($type), true);
    }
}
