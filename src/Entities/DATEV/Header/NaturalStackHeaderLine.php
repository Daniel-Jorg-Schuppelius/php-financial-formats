<?php
/*
 * Created on   : Sun Dec 16 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : NaturalStackHeaderLine.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\DATEV\Header;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\HeaderLineAbstract;
use CommonToolkit\Contracts\Interfaces\CSV\FieldInterface;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\Document;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\V700\NaturalStackHeaderField;

/**
 * DATEV Natural Persons header line (column descriptions).
 * Zweite Zeile im DATEV-Format nach dem MetaHeader.
 * Arbeitet mit FieldHeaderInterface Enums.
 */
final class NaturalStackHeaderLine extends HeaderLineAbstract {
    /**
     * Factory method for V700 NaturalStack header.
     */
    public static function createV700(
        string $delimiter = Document::DEFAULT_DELIMITER,
        string $enclosure = FieldInterface::DEFAULT_ENCLOSURE
    ): self {
        return new self(NaturalStackHeaderField::class, $delimiter, $enclosure);
    }

    /**
     * Checks if this header matches V700 NaturalStack.
     */
    public function isV700NaturalStackHeader(): bool {
        return $this->isCompatibleWithEnum(NaturalStackHeaderField::class);
    }
}
