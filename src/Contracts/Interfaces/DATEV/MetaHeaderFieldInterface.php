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
 * Common interface for all header fields (versioned).
 */
interface MetaHeaderFieldInterface {
    /** Field name according to DATEV documentation. */
    public function label(): string;

    /** Regex validation pattern for the field. */
    public function pattern(): ?string;

    /** Order of the field in the header (1..N). */
    public function position(): int;

    /**
     * Indicates whether the field must be quoted according to DATEV specification.
     *
     * @see https://developer.datev.de/de/file-format/details/datev-format/format-description/header
     */
    public function isQuoted(): bool;
}
