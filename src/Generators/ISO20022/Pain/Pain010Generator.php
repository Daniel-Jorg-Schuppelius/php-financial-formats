<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain010Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain\PainGeneratorAbstract;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type10\AmendmentDetails;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type10\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type10\MandateAmendment;

/**
 * Generates pain.010 XML documents (Mandate Amendment Request).
 * 
 * Uses ExtendedDOMDocumentBuilder for optimized XML generation.
 * Supports pain.010.001.08 (ISO 20022).
 * 
 * @package CommonToolkit\Generators\ISO20022\Pain
 */
class Pain010Generator extends PainGeneratorAbstract {
    private const DEFAULT_NAMESPACE = 'urn:iso:std:iso:20022:tech:xsd:pain.010.001.08';

    public function __construct(string $namespace = self::DEFAULT_NAMESPACE) {
        parent::__construct($namespace);
    }

    /**
     * Generates XML from a pain.010 document.
     */
    public function generate(Document $document): string {
        $this->initPainDocument('MndtAmdmntReq');

        $this->addGroupHeader($document);

        foreach ($document->getMandateAmendments() as $amendment) {
            $this->addMandateAmendment($amendment);
        }

        return $this->getXml();
    }

    private function addGroupHeader(Document $document): void {
        $this->builder->addElement('GrpHdr');

        $this->builder->addChild('MsgId', $document->getMessageId());
        $this->builder->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));

        // InitgPty
        $this->builder->addElement('InitgPty');
        $this->addChildIfNotEmpty('Nm', $document->getInitiatingParty()->getName());
        $this->builder->end(); // InitgPty

        $this->builder->end(); // GrpHdr
    }

    private function addMandateAmendment(MandateAmendment $amendment): void {
        $this->builder->addElement('UndrlygAmdmntDtls');

        $mandate = $amendment->getMandate();
        $details = $amendment->getAmendmentDetails();

        // Mndt
        $this->builder->addElement('Mndt');

        $this->builder->addChild('MndtId', $mandate->getMandateId());
        $this->builder->addChild('DtOfSgntr', $this->formatDate($mandate->getDateOfSignature()));

        // Cdtr
        $this->builder->addElement('Cdtr');
        $this->addChildIfNotEmpty('Nm', $mandate->getCreditor()->getName());
        $this->builder->end(); // Cdtr

        // CdtrAcct
        if ($mandate->getCreditorAccount()->getIban()) {
            $this->builder
                ->addElement('CdtrAcct')
                ->addElement('Id')
                ->addChild('IBAN', $mandate->getCreditorAccount()->getIban())
                ->end() // Id
                ->end(); // CdtrAcct
        }

        // Dbtr
        $this->builder->addElement('Dbtr');
        $this->addChildIfNotEmpty('Nm', $mandate->getDebtor()->getName());
        $this->builder->end(); // Dbtr

        // DbtrAcct
        if ($mandate->getDebtorAccount()->getIban()) {
            $this->builder
                ->addElement('DbtrAcct')
                ->addElement('Id')
                ->addChild('IBAN', $mandate->getDebtorAccount()->getIban())
                ->end() // Id
                ->end(); // DbtrAcct
        }

        $this->builder->end(); // Mndt

        // OrgnlMndt (Amendment Details)
        $this->addAmendmentDetails($details);

        $this->builder->end(); // UndrlygAmdmntDtls
    }

    private function addAmendmentDetails(AmendmentDetails $details): void {
        $this->builder->addElement('OrgnlMndt');

        $this->addChildIfNotEmpty('OrgnlMndtId', $details->getOriginalMandateId());

        if ($details->getOriginalCreditorSchemeId()) {
            $this->builder
                ->addElement('OrgnlCdtrSchmeId')
                ->addElement('Id')
                ->addElement('PrvtId')
                ->addElement('Othr')
                ->addChild('Id', $details->getOriginalCreditorSchemeId())
                ->end() // Othr
                ->end() // PrvtId
                ->end() // Id
                ->end(); // OrgnlCdtrSchmeId
        }

        if ($details->getOriginalDebtorAccount()) {
            $this->builder->addElement('OrgnlDbtrAcct');
            $this->builder->addElement('Id');

            if ($details->getOriginalDebtorAccount()->getIban()) {
                $this->builder->addChild('IBAN', $details->getOriginalDebtorAccount()->getIban());
            }

            $this->builder->end(); // Id
            $this->builder->end(); // OrgnlDbtrAcct
        }

        $this->builder->end(); // OrgnlMndt
    }
}
