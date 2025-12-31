<?php
/*
 * Created on   : Sun Nov 23 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DocumentLinkType.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\DATEV;

use InvalidArgumentException;

/**
 * Programmkürzel für DATEV-Beleglinks.
 */
enum DocumentLinkType: string {
    case BEDI = 'BEDI'; // Unternehmen online
    case DDMS = 'DDMS'; // DATEV DMS
    case DORG = 'DORG'; // Dokumentenablage

    public function getLabel(): string {
        return match ($this) {
            self::BEDI => 'Unternehmen online',
            self::DDMS => 'DATEV DMS',
            self::DORG => 'Dokumentenablage',
        };
    }

    public static function fromString(string $value): self {
        return match (strtoupper(trim($value))) {
            'BEDI' => self::BEDI,
            'DDMS' => self::DDMS,
            'DORG' => self::DORG,
            default => throw new InvalidArgumentException("Ungültiger Beleglink-Typ: $value"),
        };
    }
}
