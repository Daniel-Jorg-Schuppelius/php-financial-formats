<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain007Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain\GeneratorAbstract;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\OriginalPaymentInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\ReversalReason;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\TransactionInformation;

/**
 * Generator for pain.007 (Customer Payment Reversal) XML.
 * 
 * Uses ExtendedDOMDocumentBuilder for optimized XML generation.
 * 
 * @package CommonToolkit\Generators\ISO20022\Pain
 */
class Pain007Generator extends GeneratorAbstract {
    private const DEFAULT_NAMESPACE = 'urn:iso:std:iso:20022:tech:xsd:pain.007.001.12';

    public function __construct(string $namespace = self::DEFAULT_NAMESPACE) {
        parent::__construct($namespace);
    }

    /**
     * Generates pain.007 XML aus einem Document.
     */
    public function generate(Document $document): string {
        $this->initPainDocument('CstmrPmtRvsl');

        $this->addGroupHeader($document);

        if ($document->getOriginalGroupInformation() !== null) {
            $this->addOriginalGroupInformation($document);
        }

        foreach ($document->getOriginalPaymentInformations() as $info) {
            $this->addOriginalPaymentInformation($info);
        }

        return $this->getXml();
    }

    private function addGroupHeader(Document $document): void {
        $header = $document->getGroupHeader();

        $this->builder->addElement('GrpHdr');

        $this->builder->addChild('MsgId', $header->getMessageId());
        $this->builder->addChild('CreDtTm', $this->formatDateTime($header->getCreationDateTime()));

        if ($header->getNumberOfTransactions() > 0) {
            $this->builder->addChild('NbOfTxs', (string) $header->getNumberOfTransactions());
        }

        if ($header->getControlSum() !== null) {
            $this->builder->addChild('CtrlSum', $this->formatAmount($header->getControlSum()));
        }

        if ($header->isGroupReversal()) {
            $this->builder->addChild('GrpRvsl', 'true');
        }

        if ($header->getInitiatingParty() !== null) {
            $this->builder->addElement('InitgPty');
            $this->addChildIfNotEmpty('Nm', $header->getInitiatingParty()->getName());
            $this->builder->end(); // InitgPty
        }

        $this->builder->end(); // GrpHdr
    }

    private function addOriginalGroupInformation(Document $document): void {
        $orgInfo = $document->getOriginalGroupInformation();
        if ($orgInfo === null) {
            return;
        }

        $this->builder->addElement('OrgnlGrpInf');

        $this->builder->addChild('OrgnlMsgId', $orgInfo->getOriginalMessageId());
        $this->builder->addChild('OrgnlMsgNmId', $orgInfo->getOriginalMessageNameId());

        if ($orgInfo->getOriginalCreationDateTime() !== null) {
            $this->builder->addChild('OrgnlCreDtTm', $this->formatDateTime($orgInfo->getOriginalCreationDateTime()));
        }

        if ($orgInfo->getOriginalNumberOfTransactions() !== null) {
            $this->builder->addChild('OrgnlNbOfTxs', (string) $orgInfo->getOriginalNumberOfTransactions());
        }

        if ($orgInfo->getOriginalControlSum() !== null) {
            $this->builder->addChild('OrgnlCtrlSum', $this->formatAmount($orgInfo->getOriginalControlSum()));
        }

        if ($orgInfo->getReversalReason() !== null) {
            $this->addReversalReason($orgInfo->getReversalReason());
        }

        $this->builder->end(); // OrgnlGrpInf
    }

    private function addOriginalPaymentInformation(OriginalPaymentInformation $info): void {
        $this->builder->addElement('OrgnlPmtInfAndRvsl');

        $this->builder->addChild('OrgnlPmtInfId', $info->getOriginalPaymentInformationId());

        if ($info->getOriginalNumberOfTransactions() !== null) {
            $this->builder->addChild('OrgnlNbOfTxs', (string) $info->getOriginalNumberOfTransactions());
        }

        if ($info->getOriginalControlSum() !== null) {
            $this->builder->addChild('OrgnlCtrlSum', $this->formatAmount($info->getOriginalControlSum()));
        }

        if ($info->isPaymentInformationReversal()) {
            $this->builder->addChild('PmtInfRvsl', 'true');
        }

        if ($info->getReversalReason() !== null) {
            $this->addReversalReason($info->getReversalReason());
        }

        foreach ($info->getTransactionInformations() as $tx) {
            $this->addTransactionInformation($tx);
        }

        $this->builder->end(); // OrgnlPmtInfAndRvsl
    }

    private function addTransactionInformation(TransactionInformation $tx): void {
        $this->builder->addElement('TxInf');

        $this->addChildIfNotEmpty('RvslId', $tx->getReversalId());
        $this->addChildIfNotEmpty('OrgnlInstrId', $tx->getOriginalInstructionId());
        $this->builder->addChild('OrgnlEndToEndId', $tx->getOriginalEndToEndId());

        if ($tx->getReversedAmount() !== null) {
            $this->builder
                ->addElement('RvsdInstdAmt', $this->formatAmount($tx->getReversedAmount()))
                ->withAttribute('Ccy', $tx->getCurrency()?->value ?? 'EUR')
                ->end();
        }

        if ($tx->getReversalReason() !== null) {
            $this->addReversalReason($tx->getReversalReason());
        }

        $this->builder->end(); // TxInf
    }

    private function addReversalReason(ReversalReason $reason): void {
        $this->builder->addElement('RvslRsnInf');

        $this->builder->addElement('Rsn');

        if ($reason->getCodeString() !== null) {
            $this->builder->addChild('Cd', $reason->getCodeString());
        } elseif ($reason->getProprietary() !== null) {
            $this->builder->addChild('Prtry', $reason->getProprietary());
        }

        $this->builder->end(); // Rsn

        foreach ($reason->getAdditionalInfo() as $addtlInf) {
            $this->builder->addChild('AddtlInf', $addtlInf);
        }

        $this->builder->end(); // RvslRsnInf
    }
}
