<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt027Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type27\Document;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtVersion;

/**
 * Generator for CAMT.027 XML (Claim Non Receipt).
 * 
 * Generates requests for proof of a payment not received
 * according to ISO 20022 camt.027.001.xx Standard.
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt027Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT027;
    }

    public function generate(Document $document, CamtVersion $version = CamtVersion::V10): string {
        $this->initCamtDocument('ClmNonRct', $version);

        // Assgnmt (Assignment)
        $this->addAssignment($document);

        // Case (optional)
        $this->addCase($document);

        // Undrlyg (Underlying)
        $this->addUnderlying($document);

        // CoverDtls (Cover Details)
        $this->addCoverDetails($document);

        return $this->getXml();
    }

    /**
     * Adds the assignment structure.
     */
    private function addAssignment(Document $document): void {
        $this->builder->addElement('Assgnmt');

        $this->builder->addChild('Id', $this->escape($document->getAssignmentId()));
        $this->builder->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));

        // Assigner
        if ($document->getAssignerAgentBic() !== null || $document->getAssignerPartyName() !== null) {
            $this->builder->addElement('Assgnr');

            if ($document->getAssignerAgentBic() !== null) {
                $this->addAgentByBic('Agt', $document->getAssignerAgentBic());
            } elseif ($document->getAssignerPartyName() !== null) {
                $this->builder
                    ->addElement('Pty')
                    ->addChild('Nm', $this->escape($document->getAssignerPartyName()))
                    ->end();
            }

            $this->builder->end(); // Assgnr
        }

        // Assignee
        if ($document->getAssigneeAgentBic() !== null || $document->getAssigneePartyName() !== null) {
            $this->builder->addElement('Assgne');

            if ($document->getAssigneeAgentBic() !== null) {
                $this->addAgentByBic('Agt', $document->getAssigneeAgentBic());
            } elseif ($document->getAssigneePartyName() !== null) {
                $this->builder
                    ->addElement('Pty')
                    ->addChild('Nm', $this->escape($document->getAssigneePartyName()))
                    ->end();
            }

            $this->builder->end(); // Assgne
        }

        $this->builder->end(); // Assgnmt
    }

    /**
     * Adds the case structure (optional).
     */
    private function addCase(Document $document): void {
        if ($document->getCaseId() === null) {
            return;
        }

        $this->builder->addElement('Case');
        $this->builder->addChild('Id', $this->escape($document->getCaseId()));

        if ($document->getCaseCreator() !== null) {
            $this->builder
                ->addElement('Cretr')
                ->addElement('Pty')
                ->addChild('Nm', $this->escape($document->getCaseCreator()))
                ->end() // Pty
                ->end(); // Cretr
        }

        $this->builder->end(); // Case
    }

    /**
     * Adds the underlying transaction reference.
     */
    private function addUnderlying(Document $document): void {
        if ($document->getOriginalMessageId() === null && $document->getOriginalEndToEndId() === null) {
            return;
        }

        $this->builder->addElement('Undrlyg');
        $this->builder->addElement('Initn');
        $this->builder->addElement('OrgnlInstdAmt');

        // Original Group Information
        if ($document->getOriginalMessageId() !== null) {
            $this->builder->addElement('OrgnlGrpInf');
            $this->builder->addChild('OrgnlMsgId', $this->escape($document->getOriginalMessageId()));
            $this->addChildIfNotEmpty('OrgnlMsgNmId', $document->getOriginalMessageNameId());
            if ($document->getOriginalCreationDateTime() !== null) {
                $this->builder->addChild('OrgnlCreDtTm', $this->formatDateTime($document->getOriginalCreationDateTime()));
            }
            $this->builder->end(); // OrgnlGrpInf
        }

        // Original Payment Information
        $this->addChildIfNotEmpty('OrgnlEndToEndId', $document->getOriginalEndToEndId());
        $this->addChildIfNotEmpty('OrgnlTxId', $document->getOriginalTransactionId());

        // Original Amount
        if ($document->getOriginalInterbankSettlementAmount() !== null && $document->getOriginalCurrency() !== null) {
            $this->builder
                ->addElement('OrgnlIntrBkSttlmAmt', $this->formatAmount($document->getOriginalInterbankSettlementAmount()))
                ->withAttribute('Ccy', $document->getOriginalCurrency()->value)
                ->end();
        }

        if ($document->getOriginalInterbankSettlementDate() !== null) {
            $this->builder->addChild('OrgnlIntrBkSttlmDt', $this->formatDate($document->getOriginalInterbankSettlementDate()));
        }

        // Debtor
        if ($document->getDebtorName() !== null) {
            $this->builder
                ->addElement('Dbtr')
                ->addChild('Nm', $this->escape($document->getDebtorName()))
                ->end();
        }

        // Creditor
        if ($document->getCreditorName() !== null) {
            $this->builder
                ->addElement('Cdtr')
                ->addChild('Nm', $this->escape($document->getCreditorName()))
                ->end();
        }

        $this->builder->end(); // OrgnlInstdAmt
        $this->builder->end(); // Initn
        $this->builder->end(); // Undrlyg
    }

    /**
     * Adds the cover details.
     */
    private function addCoverDetails(Document $document): void {
        if ($document->getMissingCoverIndicator() === null) {
            return;
        }

        $this->builder->addElement('CoverDtls');

        $this->builder->addChild('MssngCoverInd', $document->getMissingCoverIndicator() ? 'true' : 'false');

        if ($document->getCoverDate() !== null) {
            $this->builder->addChild('CoverDt', $this->formatDate($document->getCoverDate()));
        }

        $this->builder->end(); // CoverDtls
    }
}
