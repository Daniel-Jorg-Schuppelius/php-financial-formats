<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain001Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain\PainGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type1\CreditTransferTransaction;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type1\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type1\GroupHeader;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type1\PaymentInstruction;

/**
 * Generiert pain.001 XML-Dokumente (Customer Credit Transfer Initiation).
 * 
 * Unterstützt pain.001.001.12 (ISO 20022 2024).
 * Nutzt ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * 
 * @package CommonToolkit\Generators\ISO20022\Pain
 */
class Pain001Generator extends PainGeneratorAbstract {
    private const DEFAULT_NAMESPACE = 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.12';

    public function __construct(string $namespace = self::DEFAULT_NAMESPACE) {
        parent::__construct($namespace);
    }

    /**
     * Generiert XML aus einem pain.001 Dokument.
     */
    public function generate(Document $document): string {
        $this->initPainDocument('CstmrCdtTrfInitn', 'pain.001.001.12.xsd');

        $this->addGroupHeader($document->getGroupHeader());

        foreach ($document->getPaymentInstructions() as $paymentInstruction) {
            $this->addPaymentInstruction($paymentInstruction);
        }

        return $this->getXml();
    }

    private function addGroupHeader(GroupHeader $groupHeader): void {
        $this->builder->addElement('GrpHdr');

        $this->builder->addChild('MsgId', $this->escape($groupHeader->getMessageId()));
        $this->builder->addChild('CreDtTm', $this->formatDateTime($groupHeader->getCreationDateTime()));
        $this->builder->addChild('NbOfTxs', (string) $groupHeader->getNumberOfTransactions());

        if ($groupHeader->getControlSum() !== null) {
            $this->builder->addChild('CtrlSum', $this->formatAmount($groupHeader->getControlSum()));
        }

        $this->addPainPartyIdentification('InitgPty', $groupHeader->getInitiatingParty());

        if ($groupHeader->getForwardingAgent() !== null) {
            $this->addPainFinancialInstitution('FwdgAgt', $groupHeader->getForwardingAgent());
        }

        $this->builder->end(); // GrpHdr
    }

    private function addPaymentInstruction(PaymentInstruction $instruction): void {
        $this->builder->addElement('PmtInf');

        $this->builder->addChild('PmtInfId', $this->escape($instruction->getPaymentInstructionId()));
        $this->builder->addChild('PmtMtd', $instruction->getPaymentMethod()->value);
        $this->builder->addChild('NbOfTxs', (string) $instruction->countTransactions());
        $this->builder->addChild('CtrlSum', $this->formatAmount($instruction->calculateControlSum()));

        // ReqdExctnDt
        $this->builder
            ->addElement('ReqdExctnDt')
            ->addChild('Dt', $this->formatDate($instruction->getRequestedExecutionDate()))
            ->end();

        $this->addPainPartyIdentification('Dbtr', $instruction->getDebtor());
        $this->addPainAccountIdentification('DbtrAcct', $instruction->getDebtorAccount());

        if ($instruction->getDebtorAgent() !== null) {
            $this->addPainFinancialInstitution('DbtrAgt', $instruction->getDebtorAgent());
        }

        if ($instruction->getChargeBearer() !== null) {
            $this->builder->addChild('ChrgBr', $instruction->getChargeBearer()->value);
        }

        foreach ($instruction->getTransactions() as $transaction) {
            $this->addCreditTransferTransaction($transaction);
        }

        $this->builder->end(); // PmtInf
    }

    private function addCreditTransferTransaction(CreditTransferTransaction $transaction): void {
        $this->builder->addElement('CdtTrfTxInf');

        $this->addPaymentIdentification($transaction->getPaymentId());

        // Amt
        $this->builder->addElement('Amt');
        $this->addInstructedAmount($transaction->getAmount(), $transaction->getCurrency()->value);
        $this->builder->end(); // Amt

        if ($transaction->getCreditorAgent() !== null) {
            $this->addPainFinancialInstitution('CdtrAgt', $transaction->getCreditorAgent());
        }

        $this->addPainPartyIdentification('Cdtr', $transaction->getCreditor());

        if ($transaction->getCreditorAccount() !== null) {
            $this->addPainAccountIdentification('CdtrAcct', $transaction->getCreditorAccount());
        }

        if ($transaction->getRemittanceInformation() !== null) {
            $this->addPainRemittanceInformation($transaction->getRemittanceInformation());
        }

        $this->builder->end(); // CdtTrfTxInf
    }
}
