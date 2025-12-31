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
use CommonToolkit\Contracts\Interfaces\Common\CSV\FieldInterface;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\Document;
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\V700\NaturalStackHeaderField;

/**
 * DATEV Natürliche Personen Header-Zeile (Spaltenbeschreibungen).
 * Zweite Zeile im DATEV-Format nach dem MetaHeader.
 * Arbeitet mit FieldHeaderInterface Enums.
 */
final class NaturalStackHeaderLine extends HeaderLineAbstract {
    /**
     * Factory-Methode für V700 NaturalStack Header.
     */
    public static function createV700(
        string $delimiter = Document::DEFAULT_DELIMITER,
        string $enclosure = FieldInterface::DEFAULT_ENCLOSURE
    ): self {
        return new self(NaturalStackHeaderField::class, $delimiter, $enclosure);
    }

    /**
     * Prüft ob dieser Header zu V700 NaturalStack passt.
     */
    public function isV700NaturalStackHeader(): bool {
        return $this->isCompatibleWithEnum(NaturalStackHeaderField::class);
    }
}
