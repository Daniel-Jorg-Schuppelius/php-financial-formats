<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt055Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtDocumentAbstract;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55\OriginalPaymentInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55\PaymentCancellationRequest;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55\UnderlyingTransaction;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use InvalidArgumentException;

/**
 * Generator für CAMT.055 XML (Customer Payment Cancellation Request).
 * 
 * Generiert Stornierungsanfragen vom Kunden an die Bank
 * gemäß ISO 20022 camt.055.001.xx Standard.
 * Nutzt ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt055Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT055;
    }

    /**
     * @param Document $document
     */
    public function generate(CamtDocumentAbstract $document, CamtVersion $version = CamtVersion::V12): string {
        if (!$document instanceof Document) {
            throw new InvalidArgumentException('Camt055Generator erwartet ein Camt.055 Document.');
        }

        $this->initCamtDocument('CstmrPmtCxlReq', $version);

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
     * Fügt die Assignment-Struktur hinzu.
     */
    private function addAssignment(Document $document): void {
        $this->builder->addElement('Assgnmt');

        $this->builder->addChild('Id', $this->escape($document->getMessageId()));
        $this->builder->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));

        // Assigner (Initiating Party)
        if ($document->getInitiatingPartyName() !== null) {
            $this->builder->addElement('Assgnr');
            $this->builder->addElement('Pty');

            $this->builder->addChild('Nm', $this->escape($document->getInitiatingPartyName()));

            if ($document->getInitiatingPartyId() !== null) {
                $this->builder
                    ->addElement('Id')
                    ->addElement('OrgId')
                    ->addElement('Othr')
                    ->addChild('Id', $this->escape($document->getInitiatingPartyId()))
                    ->end() // Othr
                    ->end() // OrgId
                    ->end(); // Id
            }

            $this->builder->end(); // Pty
            $this->builder->end(); // Assgnr
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
     * Fügt die CtrlData-Struktur hinzu (optional).
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
     * Fügt eine Underlying Transaction hinzu.
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

            $this->builder->end(); // OrgnlGrpInfAndCxl
        }

        // OrgnlPmtInfAndCxl
        foreach ($underlying->getOriginalPaymentInformationAndCancellation() as $pmtInf) {
            $this->addOriginalPaymentInformationAndCancellation($pmtInf);
        }

        // Direct TxInf under Undrlyg
        foreach ($underlying->getTransactionInformation() as $txInfo) {
            $this->addTransactionInfo($txInfo);
        }

        $this->builder->end(); // Undrlyg
    }

    /**
     * Fügt Original Payment Information And Cancellation hinzu.
     */
    private function addOriginalPaymentInformationAndCancellation(OriginalPaymentInformation $pmtInf): void {
        $this->builder->addElement('OrgnlPmtInfAndCxl');

        $this->addChildIfNotEmpty('OrgnlPmtInfId', $pmtInf->getOriginalPaymentInformationId());

        if ($pmtInf->getOriginalNumberOfTransactions() !== null) {
            $this->builder->addChild('OrgnlNbOfTxs', (string)$pmtInf->getOriginalNumberOfTransactions());
        }

        $this->addChildIfNotEmpty('OrgnlCtrlSum', $pmtInf->getOriginalControlSum());

        if ($pmtInf->isCancelAllTransactions()) {
            $this->builder->addChild('PmtInfCxl', 'true');
        }

        // TxInf within OrgnlPmtInfAndCxl
        foreach ($pmtInf->getTransactionInformation() as $txInfo) {
            $this->addTransactionInfo($txInfo);
        }

        $this->builder->end(); // OrgnlPmtInfAndCxl
    }

    /**
     * Fügt Transaction Information hinzu.
     */
    private function addTransactionInfo(PaymentCancellationRequest $txInfo): void {
        $this->builder->addElement('TxInf');

        $this->addChildIfNotEmpty('CxlId', $txInfo->getCancellationId());

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

        // OrgnlInstdAmt
        if ($txInfo->getOriginalAmount() !== null && $txInfo->getOriginalCurrency() !== null) {
            $this->addAmount('OrgnlInstdAmt', $txInfo->getOriginalAmount(), $txInfo->getOriginalCurrency());
        }

        // OrgnlReqdExctnDt
        if ($txInfo->getRequestedExecutionDate() !== null) {
            $this->builder->addChild('OrgnlReqdExctnDt', $this->formatDate($txInfo->getRequestedExecutionDate()));
        }

        $this->builder->end(); // TxInf
    }
}
