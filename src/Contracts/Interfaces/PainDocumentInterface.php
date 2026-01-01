<?php
/*
 * Created on   : Thu Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PainDocumentInterface.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Interfaces;

use CommonToolkit\FinancialFormats\Enums\PainType;

/**
 * Interface für alle Pain-Dokumente.
 * 
 * Ermöglicht einheitliche Handhabung aller Pain-Formate
 * (001, 002, 007, 008, 009, etc.).
 * 
 * @package CommonToolkit\FinancialFormats\Contracts\Interfaces
 */
interface PainDocumentInterface {
    /**
     * Gibt den Pain-Typ des Dokuments zurück.
     */
    public function getType(): PainType;
}
