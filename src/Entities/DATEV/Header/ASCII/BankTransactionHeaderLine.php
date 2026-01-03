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

use CommonToolkit\Contracts\Interfaces\CSV\LineInterface;
use CommonToolkit\Entities\CSV\HeaderLine;
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

    /**
     * Retrieves the trimmed value of a specific field from a data row.
     * 
     * Typsicherer Wrapper um die Basis-Methode getValueByIndex(),
     * der das BankTransactionHeaderField Enum verwendet.
     * 
     * @param LineInterface $row Die Datenzeile
     * @param BankTransactionHeaderField $field Das gewünschte Feld
     * @return string|null Der getrimmte Feldwert oder null wenn das Feld nicht existiert
     */
    public function getFieldValue(LineInterface $row, BankTransactionHeaderField $field): ?string {
        return $this->getValueByIndex($row, $field->index());
    }

    /**
     * Checks if a specific field exists and has a non-empty value in the data row.
     * 
     * Typsicherer Wrapper um die Basis-Methode hasValueByIndex(),
     * der das BankTransactionHeaderField Enum verwendet.
     * 
     * @param LineInterface $row Die Datenzeile
     * @param BankTransactionHeaderField $field Das zu prüfende Feld
     * @return bool True wenn das Feld existiert und einen nicht-leeren Wert hat
     */
    public function hasFieldValue(LineInterface $row, BankTransactionHeaderField $field): bool {
        return $this->hasValueByIndex($row, $field->index());
    }
}
