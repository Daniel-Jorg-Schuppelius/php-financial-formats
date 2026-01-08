<?php
/*
 * Created on   : Wed Jan 08 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : SepaKeyword.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\Mt;

/**
 * SEPA/DATEV Keywords for MT940 :86: field (German banking).
 * 
 * These keywords are used in DATEV format within the ?20-?29 purpose lines.
 * Format: KEYWORD+value (e.g., "EREF+ORDER12345")
 * 
 * @see DATEV Hilfe-Center MT940 Formatbeschreibung
 */
enum SepaKeyword: string {
    /**
     * End-to-End Reference
     * Unique reference assigned by the initiating party.
     */
    case EREF = 'EREF';

    /**
     * Customer Reference (Kundenreferenz)
     * Customer's own reference for the payment.
     */
    case KREF = 'KREF';

    /**
     * Mandate Reference
     * Unique reference of the mandate for direct debit.
     */
    case MREF = 'MREF';

    /**
     * Creditor Identifier
     * Gläubiger-Identifikation for direct debit.
     */
    case CRED = 'CRED';

    /**
     * Debtor Identifier
     * Schuldner-Identifikation.
     */
    case DEBT = 'DEBT';

    /**
     * SEPA Verwendungszweck (Remittance Information)
     * The main purpose/description field.
     */
    case SVWZ = 'SVWZ';

    /**
     * Abweichender Auftraggeber
     * Different ordering party name.
     */
    case ABWA = 'ABWA';

    /**
     * Abweichender Zahlungsempfänger
     * Different beneficiary name.
     */
    case ABWE = 'ABWE';

    /**
     * IBAN of counterparty
     */
    case IBAN = 'IBAN';

    /**
     * BIC of counterparty
     */
    case BIC = 'BIC';

    /**
     * Original Amount (for returns)
     */
    case OAMT = 'OAMT';

    /**
     * Compensation Amount
     */
    case COAM = 'COAM';

    /**
     * Date of original transaction (for returns)
     */
    case DATE = 'DATE';

    /**
     * Returns the description.
     */
    public function description(): string {
        return match ($this) {
            self::EREF => 'End-to-End Reference',
            self::KREF => 'Customer Reference',
            self::MREF => 'Mandate Reference',
            self::CRED => 'Creditor Identifier',
            self::DEBT => 'Debtor Identifier',
            self::SVWZ => 'Remittance Information',
            self::ABWA => 'Different Ordering Party',
            self::ABWE => 'Different Beneficiary',
            self::IBAN => 'IBAN',
            self::BIC  => 'BIC',
            self::OAMT => 'Original Amount',
            self::COAM => 'Compensation Amount',
            self::DATE => 'Original Date',
        };
    }

    /**
     * Returns the German description.
     */
    public function descriptionDe(): string {
        return match ($this) {
            self::EREF => 'End-to-End-Referenz',
            self::KREF => 'Kundenreferenz',
            self::MREF => 'Mandatsreferenz',
            self::CRED => 'Gläubiger-Identifikation',
            self::DEBT => 'Schuldner-Identifikation',
            self::SVWZ => 'SEPA-Verwendungszweck',
            self::ABWA => 'Abweichender Auftraggeber',
            self::ABWE => 'Abweichender Zahlungsempfänger',
            self::IBAN => 'IBAN',
            self::BIC  => 'BIC',
            self::OAMT => 'Ursprungsbetrag',
            self::COAM => 'Ausgleichsbetrag',
            self::DATE => 'Ursprungsdatum',
        };
    }

    /**
     * Returns the maximum length for this keyword's value.
     */
    public function maxLength(): int {
        return match ($this) {
            self::EREF, self::KREF, self::MREF, self::CRED, self::DEBT => 35,
            self::SVWZ                                                 => 140,
            self::ABWA, self::ABWE                                     => 70,
            self::IBAN                                                 => 34,
            self::BIC                                                  => 11,
            self::OAMT, self::COAM                                     => 18,
            self::DATE                                                 => 8,
        };
    }

    /**
     * Formats a value with this keyword (DATEV format).
     */
    public function format(string $value): string {
        return $this->value . '+' . substr($value, 0, $this->maxLength());
    }

    /**
     * Extracts this keyword's value from a text.
     * Returns null if not found.
     */
    public function extract(string $text): ?string {
        // Keywords that could follow this one
        $allKeywords = array_map(fn($k) => $k->value, self::cases());
        $keywordPattern = implode('|', $allKeywords);

        $pattern = '/' . preg_quote($this->value, '/') . '\+(.*?)(?=(?:' . $keywordPattern . ')\+|$)/s';

        if (preg_match($pattern, $text, $match)) {
            return trim($match[1]);
        }
        return null;
    }

    /**
     * Parses all SEPA keywords from a text.
     * Returns an associative array [keyword => value].
     */
    public static function parseAll(string $text): array {
        $result = [];
        foreach (self::cases() as $keyword) {
            $value = $keyword->extract($text);
            if ($value !== null) {
                $result[$keyword->value] = $value;
            }
        }
        return $result;
    }
}