<?php
/*
 * Created on   : Sun Dec 16 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DebitorsCreditorsHeaderLine.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\DATEV\Header;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\HeaderLineAbstract;
use CommonToolkit\Contracts\Interfaces\CSV\FieldInterface;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\Document;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\V700\DebitorsCreditorsHeaderField;

/**
 * DATEV Debitoren/Kreditoren Header-Zeile (Spaltenbeschreibungen).
 * Zweite Zeile im DATEV-Format nach dem MetaHeader.
 * Arbeitet mit FieldHeaderInterface Enums.
 */
final class DebitorsCreditorsHeaderLine extends HeaderLineAbstract {
    /**
     * Factory method for V700 Debitors/Creditors header.
     */
    public static function createV700(
        string $delimiter = Document::DEFAULT_DELIMITER,
        string $enclosure = FieldInterface::DEFAULT_ENCLOSURE
    ): self {
        return new self(DebitorsCreditorsHeaderField::class, $delimiter, $enclosure);
    }

    /**
     * Checks if this header matches V700 Debitors/Creditors.
     */
    public function isV700DebitorsCreditorsHeader(): bool {
        return $this->isCompatibleWithEnum(DebitorsCreditorsHeaderField::class);
    }
}
