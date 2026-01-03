<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : SequenceType.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums;

/**
 * Sequence Type for SEPA direct debits (SeqTp).
 * 
 * Definiert die Sequenz der Lastschrift im Mandatskontext.
 * 
 * @package CommonToolkit\Enums\Common\Banking
 */
enum SequenceType: string {
    case FIRST = 'FRST';      // Erstlastschrift
    case RECURRING = 'RCUR';  // Wiederkehrende Lastschrift
    case FINAL = 'FNAL';      // Letzte Lastschrift
    case ONE_OFF = 'OOFF';    // Einmalige Lastschrift

    /**
     * Returns the description.
     */
    public function description(): string {
        return match ($this) {
            self::FIRST => 'Erstlastschrift',
            self::RECURRING => 'Wiederkehrende Lastschrift',
            self::FINAL => 'Letzte Lastschrift',
            self::ONE_OFF => 'Einmalige Lastschrift',
        };
    }

    /**
     * SEPA default value for one-time direct debits.
     */
    public static function defaultSepa(): self {
        return self::ONE_OFF;
    }

    /**
     * Checks if mandate is required.
     */
    public function requiresMandate(): bool {
        return true; // Alle SEPA-Lastschriften erfordern ein Mandat
    }
}
