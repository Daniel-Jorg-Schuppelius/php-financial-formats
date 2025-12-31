<?php
/*
 * Created on   : Mon Dec 01 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mark.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields;

enum Mark: string {
    case EXTF = 'EXTF'; // Export aus 3rd-Party App
    case DTVF = 'DTVF'; // Export aus DATEV App

    /**
     * DATEV-Metaheader Regex-Muster für dieses Enum.
     */
    public static function pattern(): string {
        return '^(EXTF|DTVF)$';
    }
}
