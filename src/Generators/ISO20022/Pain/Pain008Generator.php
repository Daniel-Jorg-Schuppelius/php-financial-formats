<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain008Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain\GeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\DirectDebitTransaction;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\MandateInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\PaymentInstruction;

/**
 * Generator for pain.008 (Customer Direct Debit Initiation) XML.
 * 
 * Uses ExtendedDOMDocumentBuilder for optimized XML generation.
 * 
 * @package CommonToolkit\Generators\ISO20022\Pain
 */
class Pain008Generator extends GeneratorAbstract {
    private const DEFAULT_NAMESPACE = 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.11';

    public function __construct(string $namespace = self::DEFAULT_NAMESPACE) {
        parent::__construct($namespace);
    }

    /**
     * Generates pain.008 XML aus einem Document.
     */
    public function generate(Document $document): string {
        $this->initPainDocument('CstmrDrctDbtInitn');

        $this->addGroupHeader($document);

        foreach ($document->getPaymentInstructions() as $instruction) {
            $this->addPaymentInstruction($instruction, $document);
        }

        return $this->getXml();
    }

    private function addGroupHeader(Document $document): void {
        $header = $document->getGroupHeader();

        $this->builder->addElement('GrpHdr');

        $this->builder->addChild('MsgId', $this->escape($header->getMessageId()));
        $this->builder->addChild('CreDtTm', $this->formatDateTime($header->getCreationDateTime()));
        $this->builder->addChild('NbOfTxs', (string) $document->countTransactions());
        $this->builder->addChild('CtrlSum', $this->formatAmount($document->calculateControlSum()));

        $this->builder->addElement('InitgPty');
        $this->addChildIfNotEmpty('Nm', $header->getInitiatingParty()->getName());
        $this->builder->end(); // InitgPty

        $this->builder->end(); // GrpHdr
    }

    private function addPaymentInstruction(PaymentInstruction $instruction, Document $document): void {
        $this->builder->addElement('PmtInf');

        $this->builder->addChild('PmtInfId', $this->escape($instruction->getPaymentInstructionId()));
        $this->builder->addChild('PmtMtd', $instruction->getPaymentMethod()->value);
        $this->builder->addChild('NbOfTxs', (string) $instruction->countTransactions());
        $this->builder->addChild('CtrlSum', $this->formatAmount($instruction->calculateControlSum()));

        // PmtTpInf
        $this->builder->addElement('PmtTpInf');

        if ($instruction->getServiceLevel() !== null) {
            $this->builder
                ->addElement('SvcLvl')
                ->addChild('Cd', $this->escape($instruction->getServiceLevel()))
                ->end();
        }

        if ($instruction->getLocalInstrument() !== null) {
            $this->builder
                ->addElement('LclInstrm')
                ->addChild('Cd', $instruction->getLocalInstrument()->value)
                ->end();
        }

        if ($instruction->getSequenceType() !== null) {
            $this->builder->addChild('SeqTp', $instruction->getSequenceType()->value);
        }

        $this->builder->end(); // PmtTpInf

        // ReqdColltnDt
        $this->builder->addChild('ReqdColltnDt', $this->formatDate($instruction->getRequestedCollectionDate()));

        // Cdtr
        $this->addPainPartyIdentification('Cdtr', $instruction->getCreditor());

        // CdtrAcct
        $this->addPainAccountIdentification('CdtrAcct', $instruction->getCreditorAccount());

        // CdtrAgt
        $this->addPainFinancialInstitution('CdtrAgt', $instruction->getCreditorAgent());

        // CdtrSchmeId
        if ($instruction->getCreditorSchemeId() !== null) {
            $this->addCreditorSchemeId($instruction->getCreditorSchemeId());
        }

        // ChrgBr
        if ($instruction->getChargeBearer() !== null) {
            $this->builder->addChild('ChrgBr', $instruction->getChargeBearer()->value);
        }

        // DrctDbtTxInf
        foreach ($instruction->getTransactions() as $transaction) {
            $this->addTransaction($transaction);
        }

        $this->builder->end(); // PmtInf
    }

    private function addCreditorSchemeId(string $schemeId): void {
        $this->builder
            ->addElement('CdtrSchmeId')
            ->addElement('Id')
            ->addElement('PrvtId')
            ->addElement('Othr')
            ->addChild('Id', $this->escape($schemeId))
            ->addElement('SchmeNm')
            ->addChild('Prtry', 'SEPA')
            ->end() // SchmeNm
            ->end() // Othr
            ->end() // PrvtId
            ->end() // Id
            ->end(); // CdtrSchmeId
    }

    private function addTransaction(DirectDebitTransaction $transaction): void {
        $this->builder->addElement('DrctDbtTxInf');

        // PmtId
        $this->builder->addElement('PmtId');
        $this->addChildIfNotEmpty('InstrId', $transaction->getPaymentId()->getInstructionId());
        $this->builder->addChild('EndToEndId', $this->escape($transaction->getPaymentId()->getEndToEndId()));
        $this->builder->end(); // PmtId

        // InstdAmt
        $this->addInstructedAmount($transaction->getAmount(), $transaction->getCurrency()->value);

        // DrctDbtTx
        $this->builder->addElement('DrctDbtTx');
        $this->addMandateInfo($transaction->getMandateInfo());
        $this->builder->end(); // DrctDbtTx

        // DbtrAgt
        if ($transaction->getDebtorAgent() !== null) {
            $this->addPainFinancialInstitution('DbtrAgt', $transaction->getDebtorAgent());
        }

        // Dbtr
        $this->addPainPartyIdentification('Dbtr', $transaction->getDebtor());

        // DbtrAcct
        $this->addPainAccountIdentification('DbtrAcct', $transaction->getDebtorAccount());

        // RmtInf
        if ($transaction->getRemittanceInformation() !== null) {
            $this->addPainRemittanceInformation($transaction->getRemittanceInformation());
        }

        $this->builder->end(); // DrctDbtTxInf
    }

    private function addMandateInfo(MandateInformation $info): void {
        $this->builder->addElement('MndtRltdInf');

        $this->builder->addChild('MndtId', $this->escape($info->getMandateId()));
        $this->builder->addChild('DtOfSgntr', $this->formatDate($info->getDateOfSignature()));

        if ($info->getAmendmentIndicator() !== null) {
            $this->builder->addChild('AmdmntInd', $info->isAmended() ? 'true' : 'false');

            if ($info->isAmended()) {
                $this->builder->addElement('AmdmntInfDtls');

                $this->addChildIfNotEmpty('OrgnlMndtId', $info->getOriginalMandateId());

                if ($info->getOriginalCreditorSchemeId() !== null) {
                    $this->builder
                        ->addElement('OrgnlCdtrSchmeId')
                        ->addElement('Id')
                        ->addElement('PrvtId')
                        ->addElement('Othr')
                        ->addChild('Id', $this->escape($info->getOriginalCreditorSchemeId()))
                        ->end() // Othr
                        ->end() // PrvtId
                        ->end() // Id
                        ->end(); // OrgnlCdtrSchmeId
                }

                $this->builder->end(); // AmdmntInfDtls
            }
        }

        $this->builder->end(); // MndtRltdInf
    }
}
