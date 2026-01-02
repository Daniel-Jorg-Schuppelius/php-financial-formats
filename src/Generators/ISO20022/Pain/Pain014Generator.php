<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain014Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain\PainGeneratorAbstract;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type14\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type14\PaymentActivationStatus;

/**
 * Generiert pain.014 XML-Dokumente (Creditor Payment Activation Request Status Report).
 * 
 * Verwendet ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * Unterstützt pain.014.001.11 (ISO 20022).
 * 
 * @package CommonToolkit\Generators\ISO20022\Pain
 */
class Pain014Generator extends PainGeneratorAbstract {
    private const DEFAULT_NAMESPACE = 'urn:iso:std:iso:20022:tech:xsd:pain.014.001.11';

    public function __construct(string $namespace = self::DEFAULT_NAMESPACE) {
        parent::__construct($namespace);
    }

    /**
     * Generiert XML aus einem pain.014 Dokument.
     */
    public function generate(Document $document): string {
        $this->initPainDocument('CdtrPmtActvtnReqStsRpt');

        $this->addGroupHeader($document);
        $this->addOriginalGroupInfoAndStatus($document);
        $this->addPaymentStatuses($document);

        return $this->getXml();
    }

    private function addGroupHeader(Document $document): void {
        $this->builder->addElement('GrpHdr');

        $this->builder->addChild('MsgId', $document->getMessageId());
        $this->builder->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));

        // InitgPty
        if ($document->getInitiatingParty()) {
            $this->builder->addElement('InitgPty');
            $this->addChildIfNotEmpty('Nm', $document->getInitiatingParty()->getName());
            $this->builder->end(); // InitgPty
        }

        $this->builder->end(); // GrpHdr
    }

    private function addOriginalGroupInfoAndStatus(Document $document): void {
        $this->builder->addElement('OrgnlGrpInfAndSts');

        $this->builder->addChild('OrgnlMsgId', $document->getOriginalMessageId());
        $this->builder->addChild('OrgnlMsgNmId', $document->getOriginalMessageNameId());

        if ($document->getOriginalNumberOfTransactions() > 0) {
            $this->builder->addChild('OrgnlNbOfTxs', (string) $document->getOriginalNumberOfTransactions());
        }

        if ($document->getOriginalControlSum() > 0) {
            $this->builder->addChild('OrgnlCtrlSum', $this->formatAmount($document->getOriginalControlSum()));
        }

        $this->builder->end(); // OrgnlGrpInfAndSts
    }

    private function addPaymentStatuses(Document $document): void {
        $this->builder->addElement('OrgnlPmtInfAndSts');

        foreach ($document->getPaymentStatuses() as $status) {
            $this->addPaymentStatus($status);
        }

        $this->builder->end(); // OrgnlPmtInfAndSts
    }

    private function addPaymentStatus(PaymentActivationStatus $status): void {
        $this->builder->addElement('TxInfAndSts');

        $this->builder->addChild('OrgnlInstrId', $status->getOriginalInstructionId());
        $this->builder->addChild('OrgnlEndToEndId', $status->getOriginalEndToEndId());
        $this->builder->addChild('TxSts', $status->getStatus()->value);

        if ($status->getOriginalAmount() !== null) {
            $this->builder
                ->addElement('OrgnlInstdAmt', $this->formatAmount($status->getOriginalAmount()))
                ->withAttribute('Ccy', 'EUR')
                ->end();
        }

        if ($status->getStatusReason()) {
            $this->builder->addElement('StsRsnInf');
            $this->builder->addElement('Rsn');

            if ($status->getStatusReason()->getCode()) {
                $this->builder->addChild('Cd', $status->getStatusReason()->getCode());
            } elseif ($status->getStatusReason()->getProprietary()) {
                $this->builder->addChild('Prtry', $status->getStatusReason()->getProprietary());
            }

            $this->builder->end(); // Rsn
            $this->builder->end(); // StsRsnInf
        }

        $this->builder->end(); // TxInfAndSts
    }
}
