<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain011Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain\PainGeneratorAbstract;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type11\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type11\MandateCancellation;

/**
 * Generiert pain.011 XML-Dokumente (Mandate Cancellation Request).
 * 
 * Verwendet ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * Unterstützt pain.011.001.08 (ISO 20022).
 * 
 * @package CommonToolkit\Generators\ISO20022\Pain
 */
class Pain011Generator extends PainGeneratorAbstract {
    private const DEFAULT_NAMESPACE = 'urn:iso:std:iso:20022:tech:xsd:pain.011.001.08';

    public function __construct(string $namespace = self::DEFAULT_NAMESPACE) {
        parent::__construct($namespace);
    }

    /**
     * Generiert XML aus einem pain.011 Dokument.
     */
    public function generate(Document $document): string {
        $this->initPainDocument('MndtCxlReq');

        $this->addGroupHeader($document);

        foreach ($document->getMandateCancellations() as $cancellation) {
            $this->addMandateCancellation($cancellation);
        }

        return $this->getXml();
    }

    private function addGroupHeader(Document $document): void {
        $this->builder->addElement('GrpHdr');

        $this->builder->addChild('MsgId', $document->getMessageId());
        $this->builder->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));

        // InitgPty
        $this->builder->addElement('InitgPty');
        $this->addChildIfNotEmpty('Nm', $document->getInitiatingParty()->getName());
        $this->builder->end(); // InitgPty

        $this->builder->end(); // GrpHdr
    }

    private function addMandateCancellation(MandateCancellation $cancellation): void {
        $this->builder->addElement('UndrlygCxlDtls');

        // OrgnlMndt
        $this->builder->addElement('OrgnlMndt');

        $this->builder->addChild('MndtId', $cancellation->getMandateId());

        // Original Mandate Details wenn vorhanden
        if ($originalMandate = $cancellation->getOriginalMandate()) {
            $this->builder->addChild('DtOfSgntr', $this->formatDate($originalMandate->getDateOfSignature()));

            // Cdtr
            if ($originalMandate->getCreditor()->getName()) {
                $this->builder
                    ->addElement('Cdtr')
                    ->addChild('Nm', $originalMandate->getCreditor()->getName())
                    ->end(); // Cdtr
            }

            // CdtrSchmeId
            if ($originalMandate->getCreditorSchemeId()) {
                $this->addCreditorSchemeId($originalMandate->getCreditorSchemeId());
            }

            // Dbtr
            if ($originalMandate->getDebtor()->getName()) {
                $this->builder
                    ->addElement('Dbtr')
                    ->addChild('Nm', $originalMandate->getDebtor()->getName())
                    ->end(); // Dbtr
            }

            // DbtrAcct
            if ($originalMandate->getDebtorAccount()->getIban()) {
                $this->builder
                    ->addElement('DbtrAcct')
                    ->addElement('Id')
                    ->addChild('IBAN', $originalMandate->getDebtorAccount()->getIban())
                    ->end() // Id
                    ->end(); // DbtrAcct
            }
        }

        $this->builder->end(); // OrgnlMndt

        // CxlRsn
        $this->addCancellationReason($cancellation);

        $this->builder->end(); // UndrlygCxlDtls
    }

    private function addCreditorSchemeId(string $schemeId): void {
        $this->builder
            ->addElement('CdtrSchmeId')
            ->addElement('Id')
            ->addElement('PrvtId')
            ->addElement('Othr')
            ->addChild('Id', $schemeId)
            ->end() // Othr
            ->end() // PrvtId
            ->end() // Id
            ->end(); // CdtrSchmeId
    }

    private function addCancellationReason(MandateCancellation $cancellation): void {
        $reason = $cancellation->getCancellationReason();

        $this->builder->addElement('CxlRsn');
        $this->builder->addElement('Rsn');

        if ($reason->getCode()) {
            $this->builder->addChild('Cd', $reason->getCode());
        } elseif ($reason->getProprietary()) {
            $this->builder->addChild('Prtry', $reason->getProprietary());
        }

        $this->builder->end(); // Rsn

        foreach ($reason->getAdditionalInfo() as $addtlInf) {
            $this->builder->addChild('AddtlInf', $addtlInf);
        }

        $this->builder->end(); // CxlRsn
    }
}
