<?php
/*
 * Created on   : Sun Nov 23 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : MetaHeaderFieldInterface.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Interfaces\DATEV;

/**
 * Gemeinsame Schnittstelle für alle Header-Felder (versioniert).
 */
interface MetaHeaderFieldInterface {
    /** Feldbezeichnung laut DATEV-Dokumentation. */
    public function label(): string;

    /** Regex-Validierungsmuster für das Feld. */
    public function pattern(): ?string;

    /** Reihenfolge des Felds im Header (1..N). */
    public function position(): int;

    /**
     * Gibt an, ob das Feld laut DATEV-Spezifikation gequotet sein muss.
     *
     * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/header
     */
    public function isQuoted(): bool;
}
