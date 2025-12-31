<?php
/*
 * Created on   : Sun Nov 23 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : AccountingPurpose.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields;

enum AccountingPurpose: int {
    case INDEPENDENT       = 0;  // Unabhängig
    case TAX_LAW           = 30; // Steuerrecht
    case CALCULATION       = 40; // Kalkulatorik
    case COMMERCIAL_LAW    = 50; // Handelsrecht
    case IFRS              = 64; // IFRS
}
