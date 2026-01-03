<?php
/*
 * Created on   : Sun Dec 15 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : NaturalStackHeaderField.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\V700;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\DATEV\FieldHeaderInterface;
use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\Category;

/**
 * DATEV Natural-Stapel - Feldheader V700.
 * Complete implementation of all 15 DATEV fields for Natural Stack (agriculture/forestry)
 * basierend auf der offiziellen DATEV-Spezifikation.
 * 
 * @see https://developer.datev.de/de/file-format/details/datev-format/appendix/natural-stack
 */
enum NaturalStackHeaderField: string implements FieldHeaderInterface {
    // Spalten 1-15: Natural-Stapel für Land-/Forstwirtschaft
    case Textschluessel                 = 'Textschlüssel';                        // 1
    case Art                            = 'Art';                                  // 2
    case Stueck                         = 'Stück';                                // 3
    case Gewicht                        = 'Gewicht';                              // 4
    case Beleg                          = 'Beleg';                                // 5
    case Datum                          = 'Datum';                                // 6
    case AnFuerTextschluessel           = 'An/Für Textschlüssel';                 // 7
    case Text                           = 'Text';                                 // 8
    case Entnahmekonto                  = 'Entnahmekonto';                        // 9
    case Gesellschaftername             = 'Gesellschaftername';                   // 10
    case Beteiligtennummer              = 'Beteiligtennummer';                    // 11
    case Identifikationsnummer          = 'Identifikationsnummer';                // 12
    case Zeichnernummer                 = 'Zeichnernummer';                       // 13
    case HerkunftKz                     = 'Herkunft-Kz';                          // 14
    case Abschlussnummer                = 'Abschlussnummer';                      // 15

    /**
     * Liefert alle 15 Felder in der korrekten DATEV-Reihenfolge.
     */
    public static function ordered(): array {
        return [
            self::Textschluessel,             // 1
            self::Art,                        // 2
            self::Stueck,                     // 3
            self::Gewicht,                    // 4
            self::Beleg,                      // 5
            self::Datum,                      // 6
            self::AnFuerTextschluessel,       // 7
            self::Text,                       // 8
            self::Entnahmekonto,              // 9
            self::Gesellschaftername,         // 10
            self::Beteiligtennummer,          // 11
            self::Identifikationsnummer,      // 12
            self::Zeichnernummer,             // 13
            self::HerkunftKz,                 // 14
            self::Abschlussnummer,            // 15
        ];
    }

    /**
     * Liefert alle verpflichtenden Felder.
     */
    public static function required(): array {
        return [
            self::Textschluessel,             // Pflichtfeld: Textschlüssel gem. SKR14
            self::Art,                        // Pflichtfeld: Art der Bewegung
            self::Datum,                      // Pflichtfeld: Datum (TTMM Format)
            // AnFuerTextschluessel ist bei bestimmten Arten Pflichtfeld
        ];
    }

    /**
     * Liefert alle optionalen Felder.
     */
    public static function optional(): array {
        return array_diff(self::ordered(), self::required());
    }

    /**
     * Returns the data type for DATEV validation.
     */
    public function getDataType(): string {
        return match ($this) {
            self::Textschluessel, self::Art, self::Stueck, self::Gewicht,
            self::AnFuerTextschluessel, self::Entnahmekonto,
            self::Beteiligtennummer, self::Abschlussnummer => 'integer',

            self::Beleg, self::Datum, self::Text, self::Gesellschaftername,
            self::Identifikationsnummer, self::Zeichnernummer,
            self::HerkunftKz => 'string',
        };
    }

    /**
     * Returns the maximum field length for DATEV.
     */
    public function getMaxLength(): ?int {
        return match ($this) {
            self::Textschluessel => 9,                // Textschlüssel 1-9 Stellen
            self::Art => 2,                           // Art 1-2 Stellen
            self::Stueck => 8,                        // Stück 1-8 Stellen
            self::Gewicht => 8,                       // Gewicht 1-8 Stellen
            self::Beleg => 36,                        // Beleg max. 36 Zeichen
            self::Datum => 4,                         // TTMM Format
            self::AnFuerTextschluessel => 4,          // An/Für Textschlüssel 0-4 Stellen
            self::Text => 60,                         // Text max. 60 Zeichen
            self::Entnahmekonto => 9,                 // Entnahmekonto max. 9 Stellen
            self::Gesellschaftername => 76,           // Gesellschaftername max. 76 Zeichen
            self::Beteiligtennummer => 4,             // Beteiligtennummer max. 4 Stellen
            self::Identifikationsnummer => 11,        // Identifikationsnummer max. 11 Zeichen
            self::Zeichnernummer => 20,               // Zeichnernummer max. 20 Zeichen
            self::HerkunftKz => 2,                    // Herkunft-Kz max. 2 Zeichen
            self::Abschlussnummer => 2,               // Abschlussnummer max. 2 Stellen
        };
    }

    /**
     * Returns the regex pattern for DATEV validation.
     */
    public function getValidationPattern(): ?string {
        return match ($this) {
            // Textschlüssel: 1-9 Stellen Nummer gem. SKR14
            self::Textschluessel => '^[\d]{1,9}$',

            // Art: 2-stellig (2=Erzeugung, 21=Versetzung, 24=Verfüttert, etc.)
            self::Art => '^[\d]{1,2}$',

            // Stück: 1-8 Stellen bei Tieren
            self::Stueck => '^[\d]{1,8}$',

            // Gewicht: 1-8 Stellen bei anderen Textschlüsseln
            self::Gewicht => '^[\d]{1,8}$',

            // Beleg: Beleg-Nr. in Anführungszeichen, max. 36 Zeichen
            self::Beleg => '^["](.){0,36}["]$',

            // Datum: TTMM Format (Tag-Monat)
            self::Datum => '^((0[1-9]|[1-2][\d]|3[0-1])(0[1-9]|1[0-2])){1}$',

            // An/Für Textschlüssel: Optional 0-4 Stellen
            self::AnFuerTextschluessel => '^[\d]{0,4}$',

            // Text: Buchungstext in Anführungszeichen, max. 60 Zeichen
            self::Text => '^["](.){0,60}["]$',

            // Entnahmekonto: Optional 0-9 Stellen
            self::Entnahmekonto => '^[\d]{0,9}$',

            // Gesellschaftername: Für Naturalentnahmen, max. 76 Zeichen
            self::Gesellschaftername => '^["](.){0,76}["]$',

            // Beteiligtennummer: Optional 0-4 Stellen
            self::Beteiligtennummer => '^[\d]{0,4}$',

            // Identifikationsnummer: Optional max. 11 Zeichen
            self::Identifikationsnummer => '^["](.){0,11}["]$',

            // Zeichnernummer: Optional max. 20 Zeichen
            self::Zeichnernummer => '^["](.){0,20}["]$',

            // Herkunft-Kz: Stapelkennzeichen max. 2 Zeichen
            self::HerkunftKz => '^["](.){0,2}["]$',

            // Abschlussnummer: Mit "0" belegen, max. 2 Stellen
            self::Abschlussnummer => '^[\d]{0,2}$',
        };
    }

    /**
     * Returns the supported movement types for the type field.
     */
    public static function getSupportedMovementTypes(): array {
        return [
            2 => 'Erzeugung',
            21 => 'Versetzung',
            24 => 'Verfüttert an',
            25 => 'Verbraucht für',
            26 => 'Aussaat/Saatgut',
            27 => 'Verendet',
            28 => 'Schwund',
            29 => 'Entnahme',
        ];
    }

    /**
     * Checks if a movement type is valid.
     */
    public static function isValidMovementType(int $type): bool {
        return array_key_exists($type, self::getSupportedMovementTypes());
    }

    /**
     * Liefert die Beschreibung einer Bewegungsart.
     */
    public static function getMovementTypeDescription(int $type): ?string {
        return self::getSupportedMovementTypes()[$type] ?? null;
    }

    /**
     * Checks if the to/for text key field is required for a movement type.
     */
    public static function requiresTargetTextschluessel(int $type): bool {
        return in_array($type, [21, 24, 25]); // Versetzung, Verfüttert an, Verbraucht für
    }

    /**
     * Checks if a field is a quantity field.
     */
    public function isQuantityField(): bool {
        return in_array($this, [self::Stueck, self::Gewicht]);
    }

    /**
     * Checks if a field is a text key field.
     */
    public function isTextschluesselField(): bool {
        return in_array($this, [self::Textschluessel, self::AnFuerTextschluessel]);
    }

    /**
     * Checks if a field is used for natural withdrawals.
     */
    public function isNaturalWithdrawalField(): bool {
        return in_array($this, [
            self::Entnahmekonto,
            self::Gesellschaftername,
            self::Beteiligtennummer,
            self::Identifikationsnummer,
            self::Zeichnernummer
        ]);
    }

    /**
     * Checks if a field is an account field.
     */
    public function isAccountField(): bool {
        return $this === self::Entnahmekonto;
    }

    /**
     * Checks if the field is required.
     */
    public function isRequired(): bool {
        return in_array($this, self::required());
    }

    /**
     * Returns the position/index of the field in the field order.
     * 
     * @return int Die nullbasierte Position des Feldes
     */
    public function getPosition(): int {
        $ordered = self::ordered();
        return array_search($this, $ordered, true) ?: 0;
    }

    /**
     * Returns the DATEV category for this header format.
     */
    public static function getCategory(): Category {
        return Category::NaturalStapel;
    }

    /**
     * Returns the DATEV version for this header format.
     */
    public static function getVersion(): int {
        return 700;
    }

    /**
     * Returns the number of defined fields.
     */
    public static function getFieldCount(): int {
        return count(self::ordered());
    }

    /**
     * Checks if a field value is valid (contained in enum).
     */
    public static function isValidFieldValue(string $value): bool {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Indicates whether the FieldHeader (column heading) is enclosed in quotes.
     * DATEV-FieldHeaders werden NICHT gequoted.
     */
    public function isQuotedHeader(): bool {
        return false;
    }

    /**
     * Indicates whether the field value is enclosed in quotes.
     * Basierend auf dem Validierungspattern: Pattern mit ^["]... = gequotet
     */
    public function isQuotedValue(): bool {
        $pattern = $this->getValidationPattern();
        if ($pattern === null) {
            return true; // Default: gequotet (sicherer für Text)
        }
        // Prüfe ob Pattern mit Anführungszeichen beginnt
        return (bool) preg_match('/^\^(\(?\[?"|\(?")/u', $pattern);
    }

    /**
     * Returns the actual header name for CSV output.
     * May differ from enum value to ensure compatibility with DATEV sample files.
     */
    public function headerName(): string {
        return match ($this) {
            self::AnFuerTextschluessel => 'An/für Textschlüssel',  // Sample hat kleingeschriebenes 'f'
            default => $this->value,
        };
    }
}
