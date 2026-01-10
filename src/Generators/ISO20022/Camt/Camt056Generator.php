<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt056Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\GeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56\PaymentCancellationRequest;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56\UnderlyingTransaction;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;

/**
 * Generator for CAMT.056 XML (FI To FI Payment Cancellation Request).
 * 
 * Generates Stornierungsanfragen von Bank zu Bank
 * according to ISO 20022 camt.056.001.xx Standard.
 * Uses ExtendedDOMDocumentBuilder for optimized XML generation.
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt056Generator extends GeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT056;
    }

    public function generate(Document $document, CamtVersion $version = CamtVersion::V11): string {

        $this->initCamtDocument('FIToFIPmtCxlReq', $version);

        // Assgnmt (Assignment)
        $this->addAssignment($document);

        // Case (optional)
        $this->addCase($document);

        // CtrlData (optional)
        $this->addControlData($document);

        // Undrlyg (Underlying Transactions)
        foreach ($document->getUnderlyingTransactions() as $underlying) {
            $this->addUnderlyingTransaction($underlying);
        }

        return $this->getXml();
    }

    /**
     * Adds the assignment structure.
     */
    private function addAssignment(Document $document): void {
        $this->builder->addElement('Assgnmt');

        $this->builder->addChild('Id', $this->escape($document->getMessageId()));
        $this->builder->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));

        // Assigner (Instructing Agent)
        if ($document->getInstructingAgentBic() !== null) {
            $this->builder->addElement('Assgnr');
            $this->addAgentByBic('Agt', $document->getInstructingAgentBic());
            $this->builder->end(); // Assgnr
        }

        // Assignee (Instructed Agent)
        if ($document->getInstructedAgentBic() !== null) {
            $this->builder->addElement('Assgne');
            $this->addAgentByBic('Agt', $document->getInstructedAgentBic());
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
     * Adds the CtrlData structure (optional).
     */
    private function addControlData(Document $document): void {
        if ($document->getNumberOfTransactions() === null) {
            return;
        }

        $this->builder->addElement('CtrlData');
        $this->builder->addChild('NbOfTxs', $this->escape($document->getNumberOfTransactions()));

        $this->addChildIfNotEmpty('CtrlSum', $document->getControlSum());

        $this->builder->end(); // CtrlData
    }

    /**
     * Adds an underlying transaction.
     */
    private function addUnderlyingTransaction(UnderlyingTransaction $underlying): void {
        $this->builder->addElement('Undrlyg');

        // OrgnlGrpInfAndCxl
        if ($underlying->getOriginalGroupInformationMessageId() !== null) {
            $this->builder->addElement('OrgnlGrpInfAndCxl');

            $this->builder->addChild('OrgnlMsgId', $this->escape($underlying->getOriginalGroupInformationMessageId()));
            $this->addChildIfNotEmpty('OrgnlMsgNmId', $underlying->getOriginalGroupInformationMessageNameId());

            if ($underlying->getOriginalGroupInformationCreationDateTime() !== null) {
                $this->builder->addChild('OrgnlCreDtTm', $this->formatDateTime($underlying->getOriginalGroupInformationCreationDateTime()));
            }

            if ($underlying->getOriginalNumberOfTransactions() !== null) {
                $this->builder->addChild('OrgnlNbOfTxs', (string)$underlying->getOriginalNumberOfTransactions());
            }

            $this->addChildIfNotEmpty('OrgnlCtrlSum', $underlying->getOriginalControlSum());

            $this->builder->end(); // OrgnlGrpInfAndCxl
        }

        // TxInf (Transaction Information)
        foreach ($underlying->getTransactionInformation() as $txInfo) {
            $this->addTransactionInfo($txInfo);
        }

        $this->builder->end(); // Undrlyg
    }

    /**
     * Adds transaction information.
     */
    private function addTransactionInfo(PaymentCancellationRequest $txInfo): void {
        $this->builder->addElement('TxInf');

        // CxlRsnInf
        if ($txInfo->getCancellationReasonCode() !== null || $txInfo->getCancellationReasonProprietary() !== null) {
            $this->builder->addElement('CxlRsnInf');
            $this->builder->addElement('Rsn');

            if ($txInfo->getCancellationReasonCode() !== null) {
                $this->builder->addChild('Cd', $this->escape($txInfo->getCancellationReasonCode()));
            } else {
                $this->builder->addChild('Prtry', $this->escape($txInfo->getCancellationReasonProprietary()));
            }

            $this->builder->end(); // Rsn

            $this->addChildIfNotEmpty('AddtlInf', $txInfo->getCancellationReasonAdditionalInfo());

            $this->builder->end(); // CxlRsnInf
        }

        $this->addChildIfNotEmpty('OrgnlInstrId', $txInfo->getOriginalInstructionId());
        $this->addChildIfNotEmpty('OrgnlEndToEndId', $txInfo->getOriginalEndToEndId());
        $this->addChildIfNotEmpty('OrgnlTxId', $txInfo->getOriginalTransactionId());

        // OrgnlIntrBkSttlmAmt
        if ($txInfo->getOriginalInterbankSettlementAmount() !== null && $txInfo->getOriginalCurrency() !== null) {
            $this->addAmount('OrgnlIntrBkSttlmAmt', $txInfo->getOriginalInterbankSettlementAmount(), $txInfo->getOriginalCurrency());
        }

        // OrgnlIntrBkSttlmDt
        if ($txInfo->getOriginalInterbankSettlementDate() !== null) {
            $this->builder->addChild('OrgnlIntrBkSttlmDt', $this->formatDate($txInfo->getOriginalInterbankSettlementDate()));
        }

        $this->builder->end(); // TxInf
    }
}
