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
 * Interface for all Pain documents.
 * 
 * Extends XmlDocumentInterface with Pain-specific functionality.
 * Enables uniform handling of all Pain formats
 * (001, 002, 007, 008, 009, etc.).
 * 
 * @package CommonToolkit\FinancialFormats\Contracts\Interfaces
 */
interface PainDocumentInterface extends XmlDocumentInterface {
    /**
     * Returns the Pain type of the document.
     */
    public function getType(): PainType;

    /**
     * Generates the XML of the document.
     */
    public function toXml(): string;
}
