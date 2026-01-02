<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain013Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain\PainGeneratorAbstract;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type13\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type13\PaymentActivationRequest;

/**
 * Generiert pain.013 XML-Dokumente (Creditor Payment Activation Request).
 * 
 * Verwendet ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * Unterstützt pain.013.001.11 (ISO 20022).
 * 
 * @package CommonToolkit\Generators\ISO20022\Pain
 */
class Pain013Generator extends PainGeneratorAbstract {
    private const DEFAULT_NAMESPACE = 'urn:iso:std:iso:20022:tech:xsd:pain.013.001.11';

    public function __construct(string $namespace = self::DEFAULT_NAMESPACE) {
        parent::__construct($namespace);
    }

    /**
     * Generiert XML aus einem pain.013 Dokument.
     */
    public function generate(Document $document): string {
        $this->initPainDocument('CdtrPmtActvtnReq');

        $this->addGroupHeader($document);
        $this->addPaymentInformation($document);

        return $this->getXml();
    }

    private function addGroupHeader(Document $document): void {
        $this->builder->addElement('GrpHdr');

        $this->builder->addChild('MsgId', $document->getMessageId());
        $this->builder->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));
        $this->builder->addChild('NbOfTxs', (string) $document->countRequests());
        $this->builder->addChild('CtrlSum', $this->formatAmount($document->getControlSum()));

        // InitgPty
        $this->builder->addElement('InitgPty');
        $this->addChildIfNotEmpty('Nm', $document->getInitiatingParty()->getName());
        $this->builder->end(); // InitgPty

        $this->builder->end(); // GrpHdr
    }

    private function addPaymentInformation(Document $document): void {
        $this->builder->addElement('PmtInf');

        $this->builder->addChild('PmtInfId', $document->getMessageId() . '-PMTINF');
        $this->builder->addChild('PmtMtd', 'TRF');
        $this->builder->addChild('NbOfTxs', (string) $document->countRequests());
        $this->builder->addChild('CtrlSum', $this->formatAmount($document->getControlSum()));

        // CdtTrfTxInf
        foreach ($document->getPaymentRequests() as $request) {
            $this->addPaymentRequest($request);
        }

        $this->builder->end(); // PmtInf
    }

    private function addPaymentRequest(PaymentActivationRequest $request): void {
        $this->builder->addElement('CdtTrfTxInf');

        // PmtId
        $this->builder
            ->addElement('PmtId')
            ->addChild('InstrId', $request->getInstructionId())
            ->addChild('EndToEndId', $request->getEndToEndId())
            ->end(); // PmtId

        // Amt
        $this->builder->addElement('Amt');
        $this->addInstructedAmount($request->getAmount(), $request->getCurrency()->value);
        $this->builder->end(); // Amt

        // Dbtr
        $this->builder->addElement('Dbtr');
        $this->addChildIfNotEmpty('Nm', $request->getDebtor()->getName());
        $this->builder->end(); // Dbtr

        // DbtrAcct
        if ($request->getDebtorAccount()->getIban()) {
            $this->builder
                ->addElement('DbtrAcct')
                ->addElement('Id')
                ->addChild('IBAN', $request->getDebtorAccount()->getIban())
                ->end() // Id
                ->end(); // DbtrAcct
        }

        // DbtrAgt
        if ($request->getDebtorAgent()->getBic()) {
            $this->builder
                ->addElement('DbtrAgt')
                ->addElement('FinInstnId')
                ->addChild('BICFI', $request->getDebtorAgent()->getBic())
                ->end() // FinInstnId
                ->end(); // DbtrAgt
        }

        // Cdtr
        $this->builder->addElement('Cdtr');
        $this->addChildIfNotEmpty('Nm', $request->getCreditor()->getName());
        $this->builder->end(); // Cdtr

        // CdtrAcct
        if ($request->getCreditorAccount()->getIban()) {
            $this->builder
                ->addElement('CdtrAcct')
                ->addElement('Id')
                ->addChild('IBAN', $request->getCreditorAccount()->getIban())
                ->end() // Id
                ->end(); // CdtrAcct
        }

        // CdtrAgt
        if ($request->getCreditorAgent()->getBic()) {
            $this->builder
                ->addElement('CdtrAgt')
                ->addElement('FinInstnId')
                ->addChild('BICFI', $request->getCreditorAgent()->getBic())
                ->end() // FinInstnId
                ->end(); // CdtrAgt
        }

        // RmtInf
        if ($request->getRemittanceInformation()) {
            $this->builder
                ->addElement('RmtInf')
                ->addChild('Ustrd', $request->getRemittanceInformation())
                ->end(); // RmtInf
        }

        $this->builder->end(); // CdtTrfTxInf
    }
}
