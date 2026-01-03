<?php
/*
 * Created on   : Mon Dec 01 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BookingType.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields;

enum BookingType: int {
    case FinancialAccounting      = 1; // Finanzbuchführung (default)
    case AnnualFinancialStatement = 2; // Jahresabschluss

    /**
     * DATEV regex pattern for booking type.
     */
    public static function pattern(): string {
        return '^[1-2]$';
    }
}
