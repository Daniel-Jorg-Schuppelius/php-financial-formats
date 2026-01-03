<?php
/*
 * Created on   : Sat Dec 14 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : BookingBatchHeaderLine.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\DATEV\Header;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\HeaderLineAbstract;
use CommonToolkit\Contracts\Interfaces\CSV\FieldInterface;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\Document;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\V700\BookingBatchHeaderField;

/**
 * DATEV BookingBatch Header-Zeile (Spaltenbeschreibungen).
 * Zweite Zeile im DATEV-Format nach dem MetaHeader.
 * Version-independent - works with FieldHeaderInterface enums.
 */
final class BookingBatchHeaderLine extends HeaderLineAbstract {
    /**
     * Factory method for V700 BookingBatch header.
     */
    public static function createV700(
        string $delimiter = Document::DEFAULT_DELIMITER,
        string $enclosure = FieldInterface::DEFAULT_ENCLOSURE
    ): self {
        return new self(BookingBatchHeaderField::class, $delimiter, $enclosure);
    }

    /**
     * Checks if this header matches V700 BookingBatch.
     */
    public function isV700BookingHeader(): bool {
        return $this->isCompatibleWithEnum(BookingBatchHeaderField::class);
    }

    /**
     * Checks if this header matches V700 BookingBatch.
     */
    public function isV700BookingBatchHeader(): bool {
        return $this->isCompatibleWithEnum(BookingBatchHeaderField::class);
    }
}
