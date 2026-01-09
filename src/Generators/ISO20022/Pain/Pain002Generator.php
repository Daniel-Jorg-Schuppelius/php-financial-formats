<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain002Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain\PainGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\OriginalGroupInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\OriginalPaymentInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\StatusReason;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\TransactionInformationAndStatus;

/**
 * Generator for pain.002 (Customer Payment Status Report) XML.
 * 
 * Uses ExtendedDOMDocumentBuilder for optimized XML generation.
 * 
 * @package CommonToolkit\Generators\ISO20022\Pain
 */
class Pain002Generator extends PainGeneratorAbstract {
    private const DEFAULT_NAMESPACE = 'urn:iso:std:iso:20022:tech:xsd:pain.002.001.14';

    public function __construct(string $namespace = self::DEFAULT_NAMESPACE) {
        parent::__construct($namespace);
    }

    /**
     * Generates pain.002 XML aus einem Document.
     */
    public function generate(Document $document): string {
        $this->initPainDocument('CstmrPmtStsRpt');

        $this->addGroupHeader($document);
        $this->addOriginalGroupInformation($document->getOriginalGroupInformation());

        foreach ($document->getOriginalPaymentInformations() as $pmtInfo) {
            $this->addOriginalPaymentInformation($pmtInfo);
        }

        return $this->getXml();
    }

    private function addGroupHeader(Document $document): void {
        $header = $document->getGroupHeader();

        $this->builder->addElement('GrpHdr');
        $this->builder->addChild('MsgId', $this->escape($header->getMessageId()));
        $this->builder->addChild('CreDtTm', $this->formatDateTime($header->getCreationDateTime()));

        if ($header->getInitiatingParty() !== null) {
            $this->builder->addElement('InitgPty');
            $this->addChildIfNotEmpty('Nm', $header->getInitiatingParty()->getName());
            $this->builder->end(); // InitgPty
        }

        $this->builder->end(); // GrpHdr
    }

    private function addOriginalGroupInformation(OriginalGroupInformation $info): void {
        $this->builder->addElement('OrgnlGrpInfAndSts');

        $this->builder->addChild('OrgnlMsgId', $this->escape($info->getOriginalMessageId()));
        $this->builder->addChild('OrgnlMsgNmId', $this->escape($info->getOriginalMessageNameId()));

        if ($info->getOriginalCreationDateTime() !== null) {
            $this->builder->addChild('OrgnlCreDtTm', $this->formatDateTime($info->getOriginalCreationDateTime()));
        }

        if ($info->getOriginalNumberOfTransactions() !== null) {
            $this->builder->addChild('OrgnlNbOfTxs', (string) $info->getOriginalNumberOfTransactions());
        }

        if ($info->getOriginalControlSum() !== null) {
            $this->builder->addChild('OrgnlCtrlSum', $this->formatAmount($info->getOriginalControlSum()));
        }

        if ($info->getGroupStatus() !== null) {
            $this->builder->addChild('GrpSts', $info->getGroupStatus()->value);
        }

        foreach ($info->getStatusReasons() as $reason) {
            $this->addStatusReason($reason);
        }

        $this->builder->end(); // OrgnlGrpInfAndSts
    }

    private function addOriginalPaymentInformation(OriginalPaymentInformation $info): void {
        $this->builder->addElement('OrgnlPmtInfAndSts');

        $this->builder->addChild('OrgnlPmtInfId', $this->escape($info->getOriginalPaymentInformationId()));

        if ($info->getStatus() !== null) {
            $this->builder->addChild('PmtInfSts', $info->getStatus()->value);
        }

        foreach ($info->getStatusReasons() as $reason) {
            $this->addStatusReason($reason);
        }

        foreach ($info->getTransactionStatuses() as $txStatus) {
            $this->addTransactionStatus($txStatus);
        }

        $this->builder->end(); // OrgnlPmtInfAndSts
    }

    private function addTransactionStatus(TransactionInformationAndStatus $status): void {
        $this->builder->addElement('TxInfAndSts');

        $this->addChildIfNotEmpty('StsId', $status->getStatusId());
        $this->addChildIfNotEmpty('OrgnlInstrId', $status->getOriginalInstructionId());
        $this->addChildIfNotEmpty('OrgnlEndToEndId', $status->getOriginalEndToEndId());
        $this->addChildIfNotEmpty('OrgnlUETR', $status->getOriginalUetr());

        if ($status->getStatus() !== null) {
            $this->builder->addChild('TxSts', $status->getStatus()->value);
        }

        foreach ($status->getStatusReasons() as $reason) {
            $this->addStatusReason($reason);
        }

        if ($status->getAcceptanceDateTime() !== null) {
            $this->builder->addChild('AccptncDtTm', $this->formatDateTime($status->getAcceptanceDateTime()));
        }

        $this->builder->end(); // TxInfAndSts
    }

    private function addStatusReason(StatusReason $reason): void {
        $this->builder->addElement('StsRsnInf');

        if ($reason->getCodeString() !== null || $reason->getProprietary() !== null) {
            $this->builder->addElement('Rsn');

            if ($reason->getCodeString() !== null) {
                $this->builder->addChild('Cd', $this->escape($reason->getCodeString()));
            } elseif ($reason->getProprietary() !== null) {
                $this->builder->addChild('Prtry', $this->escape($reason->getProprietary()));
            }

            $this->builder->end(); // Rsn
        }

        foreach ($reason->getAdditionalInfo() as $info) {
            $this->builder->addChild('AddtlInf', $this->escape($info));
        }

        $this->builder->end(); // StsRsnInf
    }
}
