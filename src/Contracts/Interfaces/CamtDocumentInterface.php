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
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;

/**
 * Interface for all CAMT documents.
 * 
 * Extends XmlDocumentInterface with CAMT-specific functionality.
 * Enables uniform handling of all CAMT formats
 * (052, 053, 054, 055, 056, 026-039, 057-059, 087).
 * 
 * @package CommonToolkit\FinancialFormats\Contracts\Interfaces
 */
interface CamtDocumentInterface extends XmlDocumentInterface {
    /**
     * Returns the CAMT type of the document.
     */
    public function getCamtType(): CamtType;

    /**
     * Generates the XML of the document.
     */
    public function toXml(CamtVersion $version): string;
}
