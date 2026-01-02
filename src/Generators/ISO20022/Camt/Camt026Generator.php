<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt026Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type26\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type26\UnableToApplyReason;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;

/**
 * Generator für CAMT.026 XML (Unable to Apply).
 * 
 * Generiert Anfragen zur Klärung nicht zuordenbarer Zahlungen
 * gemäß ISO 20022 camt.026.001.xx Standard.
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt026Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT026;
    }

    public function generate(Document $document, CamtVersion $version = CamtVersion::V10): string {
        $this->initCamtDocument('UblToApply', $version);

        // Assgnmt (Assignment)
        $this->addAssignment($document);

        // Case (optional)
        $this->addCase($document);

        // Undrlyg (Underlying)
        $this->addUnderlying($document);

        // Justfn (Justification - Unable to Apply Reasons)
        $this->addJustification($document);

        return $this->getXml();
    }

    /**
     * Fügt die Assignment-Struktur hinzu.
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
     * Fügt die Case-Struktur hinzu (optional).
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
     * Fügt die Underlying Transaction Reference hinzu.
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

        $this->builder->end(); // OrgnlInstdAmt
        $this->builder->end(); // Initn
        $this->builder->end(); // Undrlyg
    }

    /**
     * Fügt die Justification (Unable to Apply Reasons) hinzu.
     */
    private function addJustification(Document $document): void {
        $reasons = $document->getUnableToApplyReasons();
        if (empty($reasons)) {
            return;
        }

        $this->builder->addElement('Justfn');

        foreach ($reasons as $reason) {
            $this->addUnableToApplyReason($reason);
        }

        $this->builder->end(); // Justfn
    }

    /**
     * Fügt einen einzelnen Unable-to-Apply-Grund hinzu.
     */
    private function addUnableToApplyReason(UnableToApplyReason $reason): void {
        $this->builder->addElement('UblToApplyRsn');

        if ($reason->getReasonCode() !== null) {
            $this->builder->addChild('Cd', $this->escape($reason->getReasonCode()));
        } elseif ($reason->getReasonProprietary() !== null) {
            $this->builder->addChild('Prtry', $this->escape($reason->getReasonProprietary()));
        }

        $this->addChildIfNotEmpty('AddtlInf', $reason->getAdditionalInformation());

        $this->builder->end(); // UblToApplyRsn
    }
}
