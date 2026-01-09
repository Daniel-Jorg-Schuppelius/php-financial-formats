<?php
/*
 * Created on   : Fri Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : TechnicalInputChannel.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 * 
 * Auto-generated from XSD: ISO_ExternalTechnicalInputChannel1Code
 * Do not edit manually - regenerate with: php tools/generate-camt-enums.php
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\ISO20022\Camt;

/**
 * TechnicalInputChannel - ISO 20022 External Code List
 * 
 * Generiert aus: ISO_ExternalTechnicalInputChannel1Code
 * @see https://www.iso20022.org/external_code_list.page
 */
enum TechnicalInputChannel: string {
    /**
     * FAXI - Fax
     * Technical Input Channel is fax or facsimile
     */
    case FAXI = 'FAXI';

    /**
     * PAPR - Paper
     * Technical Input Channel is paper
     */
    case PAPR = 'PAPR';

    /**
     * TAPE - Tape
     * Technical Input Channel is tape
     */
    case TAPE = 'TAPE';

    /**
     * WEBI - Internet
     * Technical Input Channel is internet
     */
    case WEBI = 'WEBI';

    /**
     * Gibt den Namen/Titel des Codes zurück.
     */
    public function name(): string {
        return match ($this) {
            self::FAXI => 'Fax',
            self::PAPR => 'Paper',
            self::TAPE => 'Tape',
            self::WEBI => 'Internet',
        };
    }

    /**
     * Gibt die Definition/Beschreibung des Codes zurück.
     */
    public function definition(): string {
        return match ($this) {
            self::FAXI => 'Technical Input Channel is fax or facsimile',
            self::PAPR => 'Technical Input Channel is paper',
            self::TAPE => 'Technical Input Channel is tape',
            self::WEBI => 'Technical Input Channel is internet',
        };
    }

    /**
     * Factory-Methode aus String.
     */
    public static function fromString(string $value): ?self {
        return self::tryFrom(strtoupper(trim($value)));
    }

    /**
     * Prüft ob der Wert ein gültiger Code ist.
     */
    public static function isValid(string $value): bool {
        return self::tryFrom(strtoupper(trim($value))) !== null;
    }
}
