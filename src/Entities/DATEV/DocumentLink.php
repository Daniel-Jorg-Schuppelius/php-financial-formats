<?php
/*
 * Created on   : Sun Nov 23 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DocumentLink.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\DATEV;

use CommonToolkit\FinancialFormats\Enums\DATEV\DocumentLinkType;
use InvalidArgumentException;

final class DocumentLink {
    public function __construct(
        public readonly DocumentLinkType $type,
        public readonly string $guid
    ) {
        if (!preg_match('/^[0-9A-F\-]{36}$/i', $guid)) {
            throw new InvalidArgumentException("Ungültige GUID: $guid");
        }
    }

    public function toDatevString(): string {
        // muss doppelte Quotes für CSV enthalten
        return sprintf('"%s ""%s"""', $this->type->value, $this->guid);
    }

    public static function parse(string $input): self {
        // Entferne äußere Anführungszeichen
        $input = trim($input, '"');

        // Format: BEDI "GUID"
        if (!preg_match('/^(BEDI|DDMS|DORG)\s+""?([0-9A-F\-]{36})""?$/i', $input, $m)) {
            throw new InvalidArgumentException("Ungültiger Beleglink: $input");
        }

        return new self(
            DocumentLinkType::fromString($m[1]),
            $m[2]
        );
    }

    /**
     * Gibt den Beleglink als DATEV-String zurück.
     */
    public function __toString(): string {
        return $this->toDatevString();
    }
}
