<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DocumentAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain;

use CommonToolkit\Contracts\Abstracts\XML\DomainXmlDocumentAbstract;
use CommonToolkit\FinancialFormats\Contracts\Interfaces\PainDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\PainType;

/**
 * Abstract base class for all Pain documents.
 * 
 * Inherits from DomainXmlDocumentAbstract for XmlDocumentInterface implementation.
 * 
 * Supported Pain formats:
 * - pain.001: Customer Credit Transfer Initiation
 * - pain.002: Customer Payment Status Report
 * - pain.007: Customer Payment Reversal
 * - pain.008: Customer Direct Debit Initiation
 * - pain.009: Mandate Initiation Request
 * - etc.
 * 
 * @package CommonToolkit\Contracts\Abstracts\ISO20022\Pain
 */
abstract class DocumentAbstract extends DomainXmlDocumentAbstract implements PainDocumentInterface {
    /**
     * Returns the Pain type of this document.
     */
    abstract public function getType(): PainType;

    /**
     * Generates XML output for this document.
     */
    abstract public function toXml(): string;

    /**
     * @inheritDoc
     */
    protected function getDefaultXml(): string {
        return $this->toXml();
    }
}