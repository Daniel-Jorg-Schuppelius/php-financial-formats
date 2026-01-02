<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain012Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain\PainGeneratorAbstract;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type12\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type12\MandateAcceptance;

/**
 * Generiert pain.012 XML-Dokumente (Mandate Acceptance Report).
 * 
 * Verwendet ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * Unterstützt pain.012.001.08 (ISO 20022).
 * 
 * @package CommonToolkit\Generators\ISO20022\Pain
 */
class Pain012Generator extends PainGeneratorAbstract {
    private const DEFAULT_NAMESPACE = 'urn:iso:std:iso:20022:tech:xsd:pain.012.001.08';

    public function __construct(string $namespace = self::DEFAULT_NAMESPACE) {
        parent::__construct($namespace);
    }

    /**
     * Generiert XML aus einem pain.012 Dokument.
     */
    public function generate(Document $document): string {
        $this->initPainDocument('MndtAccptncRpt');

        $this->addGroupHeader($document);
        $this->addOriginalMessageInfo($document);

        foreach ($document->getMandateAcceptances() as $acceptance) {
            $this->addMandateAcceptance($acceptance);
        }

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

    private function addOriginalMessageInfo(Document $document): void {
        $this->builder->addElement('OrgnlMsgInf');

        $this->addChildIfNotEmpty('OrgnlMsgId', $document->getOriginalMessageId());
        $this->addChildIfNotEmpty('OrgnlMsgNmId', $document->getOriginalMessageNameId());

        $this->builder->end(); // OrgnlMsgInf
    }

    private function addMandateAcceptance(MandateAcceptance $acceptance): void {
        $this->builder->addElement('UndrlygAccptncDtls');

        // OrgnlMndtId
        $this->builder->addChild('OrgnlMndtId', $acceptance->getMandateId());

        // AccptncSts
        $this->builder
            ->addElement('AccptncSts')
            ->addChild('Accptd', $acceptance->isAccepted() ? 'true' : 'false')
            ->end(); // AccptncSts

        // AccptncDtTm
        if ($acceptance->getAcceptanceDateTime()) {
            $this->builder->addChild('AccptncDtTm', $this->formatDateTime($acceptance->getAcceptanceDateTime()));
        }

        // RjctRsn
        if ($acceptance->isRejected() && $acceptance->getRejectReason()) {
            $this->builder
                ->addElement('RjctRsn')
                ->addChild('Prtry', $acceptance->getRejectReason())
                ->end(); // RjctRsn
        }

        // Mndt (wenn vorhanden)
        if ($mandate = $acceptance->getMandate()) {
            $this->builder->addElement('Mndt');
            $this->builder->addChild('MndtId', $mandate->getMandateId());
            $this->builder->addChild('DtOfSgntr', $this->formatDate($mandate->getDateOfSignature()));
            $this->builder->end(); // Mndt
        }

        $this->builder->end(); // UndrlygAccptncDtls
    }
}
