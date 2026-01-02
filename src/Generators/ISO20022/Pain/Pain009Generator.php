<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain009Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain\PainGeneratorAbstract;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Mandate;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type9\Document;

/**
 * Generiert pain.009 XML-Dokumente (Mandate Initiation Request).
 * 
 * Verwendet ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * Unterstützt pain.009.001.08 (ISO 20022).
 * 
 * @package CommonToolkit\Generators\ISO20022\Pain
 */
class Pain009Generator extends PainGeneratorAbstract {
    private const DEFAULT_NAMESPACE = 'urn:iso:std:iso:20022:tech:xsd:pain.009.001.08';

    public function __construct(string $namespace = self::DEFAULT_NAMESPACE) {
        parent::__construct($namespace);
    }

    /**
     * Generiert XML aus einem pain.009 Dokument.
     */
    public function generate(Document $document): string {
        $this->initPainDocument('MndtInitnReq');

        $this->addGroupHeader($document);

        foreach ($document->getMandates() as $mandate) {
            $this->addMandate($mandate);
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

    private function addMandate(Mandate $mandate): void {
        $this->builder->addElement('Mndt');

        $this->builder->addChild('MndtId', $mandate->getMandateId());
        $this->builder->addChild('DtOfSgntr', $this->formatDate($mandate->getDateOfSignature()));

        // MndtTpInf
        if ($mandate->getLocalInstrument() || $mandate->getSequenceType()) {
            $this->builder->addElement('MndtTpInf');

            if ($mandate->getLocalInstrument()) {
                $this->builder
                    ->addElement('LclInstrm')
                    ->addChild('Cd', $mandate->getLocalInstrument()->value)
                    ->end(); // LclInstrm
            }

            if ($mandate->getSequenceType()) {
                $this->builder->addChild('SeqTp', $mandate->getSequenceType()->value);
            }

            $this->builder->end(); // MndtTpInf
        }

        // Cdtr
        $this->addSimpleParty('Cdtr', $mandate->getCreditor()->getName());

        // CdtrAcct
        if ($mandate->getCreditorAccount()->getIban()) {
            $this->addSimpleAccount('CdtrAcct', $mandate->getCreditorAccount()->getIban());
        }

        // CdtrAgt
        if ($mandate->getCreditorAgent()->getBic()) {
            $this->addSimpleAgent('CdtrAgt', $mandate->getCreditorAgent()->getBic());
        }

        // CdtrSchmeId
        if ($mandate->getCreditorSchemeId()) {
            $this->addCreditorSchemeId($mandate->getCreditorSchemeId());
        }

        // Dbtr
        $this->addSimpleParty('Dbtr', $mandate->getDebtor()->getName());

        // DbtrAcct
        if ($mandate->getDebtorAccount()->getIban()) {
            $this->addSimpleAccount('DbtrAcct', $mandate->getDebtorAccount()->getIban());
        }

        // DbtrAgt
        if ($mandate->getDebtorAgent()->getBic()) {
            $this->addSimpleAgent('DbtrAgt', $mandate->getDebtorAgent()->getBic());
        }

        $this->builder->end(); // Mndt
    }

    private function addSimpleParty(string $tagName, ?string $name): void {
        $this->builder->addElement($tagName);
        $this->addChildIfNotEmpty('Nm', $name);
        $this->builder->end();
    }

    private function addSimpleAccount(string $tagName, string $iban): void {
        $this->builder
            ->addElement($tagName)
            ->addElement('Id')
            ->addChild('IBAN', $iban)
            ->end() // Id
            ->end(); // tagName
    }

    private function addSimpleAgent(string $tagName, string $bic): void {
        $this->builder
            ->addElement($tagName)
            ->addElement('FinInstnId')
            ->addChild('BICFI', $bic)
            ->end() // FinInstnId
            ->end(); // tagName
    }

    private function addCreditorSchemeId(string $schemeId): void {
        $this->builder
            ->addElement('CdtrSchmeId')
            ->addElement('Id')
            ->addElement('PrvtId')
            ->addElement('Othr')
            ->addChild('Id', $schemeId)
            ->addElement('SchmeNm')
            ->addChild('Prtry', 'SEPA')
            ->end() // SchmeNm
            ->end() // Othr
            ->end() // PrvtId
            ->end() // Id
            ->end(); // CdtrSchmeId
    }
}
