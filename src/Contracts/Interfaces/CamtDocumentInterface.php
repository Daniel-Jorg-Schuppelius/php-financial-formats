<?php
/*
 * Created on   : Tue Dec 31 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CamtDocumentInterface.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Interfaces;

use CommonToolkit\Contracts\Interfaces\XML\XmlDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;

/**
 * Interface für alle CAMT-Dokumente.
 * 
 * Erweitert XmlDocumentInterface um CAMT-spezifische Funktionalität.
 * Ermöglicht einheitliche Handhabung aller CAMT-Formate
 * (052, 053, 054, 055, 056, 026-039, 057-059, 087).
 * 
 * @package CommonToolkit\FinancialFormats\Contracts\Interfaces
 */
interface CamtDocumentInterface extends XmlDocumentInterface {
    /**
     * Gibt den CAMT-Typ des Dokuments zurück.
     */
    public function getCamtType(): CamtType;

    /**
     * Generiert das XML des Dokuments.
     */
    public function toXml(CamtVersion $version): string;
}
