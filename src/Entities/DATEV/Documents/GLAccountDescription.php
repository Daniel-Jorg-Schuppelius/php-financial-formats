<?php
/*
 * Created on   : Sun Dec 16 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : GLAccountDescription.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\DATEV\Documents;

use CommonToolkit\Entities\CSV\ColumnWidthConfig;
use CommonToolkit\Entities\CSV\HeaderLine;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\Document;
use CommonToolkit\FinancialFormats\Entities\DATEV\MetaHeaderLine;
use CommonToolkit\Enums\Common\CSV\TruncationStrategy;
use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\Category;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\V700\GLAccountDescriptionHeaderField;
use CommonToolkit\Enums\LanguageCode;
use RuntimeException;

/**
 * DATEV-Kontenbeschriftungen-Dokument.
 * Special document class for account labels format (Category 20).
 * 
 * Die Spaltenbreiten werden automatisch basierend auf den DATEV-Spezifikationen
 * aus GLAccountDescriptionHeaderField::getMaxLength() angewendet.
 */
final class GLAccountDescription extends Document {
    public function __construct(?MetaHeaderLine $metaHeader, ?HeaderLine $header, array $rows = []) {
        parent::__construct($metaHeader, $header, $rows);
    }

    /**
     * Erstellt eine ColumnWidthConfig basierend auf den DATEV-Spezifikationen.
     * Maximum field lengths are derived from GLAccountDescriptionHeaderField::getMaxLength().
     * 
     * @param TruncationStrategy $strategy Truncation strategy (Default: TRUNCATE for DATEV conformity)
     * @return ColumnWidthConfig
     */
    public static function createDatevColumnWidthConfig(TruncationStrategy $strategy = TruncationStrategy::TRUNCATE): ColumnWidthConfig {
        $config = new ColumnWidthConfig(null, $strategy);

        foreach (GLAccountDescriptionHeaderField::ordered() as $index => $field) {
            $maxLength = $field->getMaxLength();
            if ($maxLength !== null) {
                $config->setColumnWidth($index, $maxLength);
            }
        }

        return $config;
    }

    /**
     * Returns the DATEV category for this document type.
     */
    public function getCategory(): Category {
        return Category::Sachkontenbeschriftungen;
    }

    /**
     * Returns the DATEV format type.
     */
    public function getFormatType(): string {
        return Category::Sachkontenbeschriftungen->nameValue();
    }

    /**
     * Validiert Kontenbeschriftungen-spezifische Regeln.
     */
    public function validate(): void {
        parent::validate();

        $metaFields = $this->getMetaHeader()?->getFields() ?? [];
        if (count($metaFields) > 2 && (int)$metaFields[2]->getValue() !== 20) {
            throw new RuntimeException('Ungültige Kategorie für Kontenbeschriftungen-Dokument. Erwartet: 20');
        }
    }

    // ==== ACCOUNT FIELDS ====

    /**
     * Returns the account number (field 1).
     */
    public function getAccountNumber(int $rowIndex): ?int {
        $value = $this->getField($rowIndex, GLAccountDescriptionHeaderField::Konto);
        return $value !== null && $value !== '' && is_numeric($value) ? (int)$value : null;
    }

    /**
     * Setzt die Kontonummer (Feld 1).
     */
    public function setAccountNumber(int $rowIndex, int $accountNumber): void {
        $this->setField($rowIndex, GLAccountDescriptionHeaderField::Konto, $accountNumber);
    }

    /**
     * Returns the account label (field 2, max 40 characters).
     */
    public function getAccountDescription(int $rowIndex): ?string {
        $value = $this->getField($rowIndex, GLAccountDescriptionHeaderField::Kontenbeschriftung);
        return $value !== null && $value !== '' ? $value : null;
    }

    /**
     * Setzt die Kontenbeschriftung (Feld 2, max 40 Zeichen).
     */
    public function setAccountDescription(int $rowIndex, string $description): void {
        $this->setField($rowIndex, GLAccountDescriptionHeaderField::Kontenbeschriftung, $description);
    }

    /**
     * Returns the long account label (field 4, max 300 characters).
     */
    public function getLongAccountDescription(int $rowIndex): ?string {
        $value = $this->getField($rowIndex, GLAccountDescriptionHeaderField::KontenbeschriftungLang);
        return $value !== null && $value !== '' ? $value : null;
    }

    /**
     * Setzt die lange Kontenbeschriftung (Feld 4, max 300 Zeichen).
     */
    public function setLongAccountDescription(int $rowIndex, string $description): void {
        $this->setField($rowIndex, GLAccountDescriptionHeaderField::KontenbeschriftungLang, $description);
    }

    // ==== LANGUAGE FIELD ====

    /**
     * Returns the language ID (field 3).
     * de-DE = Deutsch, en-GB = Englisch
     */
    public function getLanguageCodeValue(int $rowIndex): ?LanguageCode {
        return parent::getLanguageCode($rowIndex, GLAccountDescriptionHeaderField::SprachID->getPosition());
    }

    /**
     * Setzt die Sprach-ID (Feld 3).
     */
    public function setLanguageCodeValue(int $rowIndex, LanguageCode $languageCode): void {
        parent::setLanguageCode($rowIndex, GLAccountDescriptionHeaderField::SprachID->getPosition(), $languageCode);
    }

    // ==== CONVENIENCE METHODS ====

    /**
     * Checks if an account label is in German.
     */
    public function isGerman(int $rowIndex): bool {
        $lang = $this->getLanguageCodeValue($rowIndex);
        return $lang !== null && $lang->isGerman();
    }

    /**
     * Checks if an account label is in English.
     */
    public function isEnglish(int $rowIndex): bool {
        $lang = $this->getLanguageCodeValue($rowIndex);
        return $lang !== null && $lang->isEnglish();
    }

    /**
     * Returns all German account labels.
     *
     * @return int[] Array der Row-Indices
     */
    public function getGermanDescriptions(): array {
        $result = [];
        $count = count($this->getRows());
        for ($i = 0; $i < $count; $i++) {
            if ($this->isGerman($i)) {
                $result[] = $i;
            }
        }
        return $result;
    }

    /**
     * Returns all English account labels.
     *
     * @return int[] Array der Row-Indices
     */
    public function getEnglishDescriptions(): array {
        $result = [];
        $count = count($this->getRows());
        for ($i = 0; $i < $count; $i++) {
            if ($this->isEnglish($i)) {
                $result[] = $i;
            }
        }
        return $result;
    }

    /**
     * Finds an account label for a specific account number and language.
     *
     * @return int|null Row-Index oder null, wenn nicht gefunden
     */
    public function findByAccountAndLanguage(int $accountNumber, LanguageCode $languageCode): ?int {
        $count = count($this->getRows());
        for ($i = 0; $i < $count; $i++) {
            if ($this->getAccountNumber($i) === $accountNumber && $this->getLanguageCodeValue($i) === $languageCode) {
                return $i;
            }
        }
        return null;
    }
}