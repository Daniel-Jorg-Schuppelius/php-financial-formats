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

use CommonToolkit\Contracts\Interfaces\XML\XmlDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\PainType;

/**
 * Interface für alle Pain-Dokumente.
 * 
 * Erweitert XmlDocumentInterface um Pain-spezifische Funktionalität.
 * Ermöglicht einheitliche Handhabung aller Pain-Formate
 * (001, 002, 007, 008, 009, etc.).
 * 
 * @package CommonToolkit\FinancialFormats\Contracts\Interfaces
 */
interface PainDocumentInterface extends XmlDocumentInterface {
    /**
     * Gibt den Pain-Typ des Dokuments zurück.
     */
    public function getType(): PainType;

    /**
     * Generiert das XML des Dokuments.
     */
    public function toXml(): string;
}
