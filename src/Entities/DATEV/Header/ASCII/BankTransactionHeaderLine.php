<?php
/*
 * Created on   : Tue Dec 23 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BankTransactionHeaderLine.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\DATEV\Header\ASCII;

use CommonToolkit\Entities\CSV\HeaderField;
use CommonToolkit\Entities\CSV\HeaderLine;
use CommonToolkit\FinancialFormats\Entities\DATEV\Header\ASCII\BankTransactionHeaderDefinition;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\ASCII\BankTransactionHeaderField;

/**
 * Header line for DATEV ASCII processing files (bank transactions).
 * 
 * This class creates virtual headers for ASCII files with 17-34 fields.
 * ASCII-Dateien haben KEINEN MetaHeader - nur einfache Datenzeilen.
 * 
 * @package CommonToolkit\Entities\DATEV\Header
 */
class BankTransactionHeaderLine extends HeaderLine {

    /**
     * Creates a standard header definition for ASCII bank transactions.
     * 
     * @return BankTransactionHeaderDefinition
     */
    public function getDefinition(): BankTransactionHeaderDefinition {
        return new BankTransactionHeaderDefinition();
    }
}
