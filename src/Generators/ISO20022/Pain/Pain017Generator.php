<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain017Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain\PainGeneratorAbstract;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type17\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type17\MandateCopyRequest;

/**
 * Generiert pain.017 XML-Dokumente (Mandate Copy Request).
 * 
 * Verwendet ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * Unterstützt pain.017.001.04 (ISO 20022).
 * 
 * @package CommonToolkit\Generators\ISO20022\Pain
 */
class Pain017Generator extends PainGeneratorAbstract {
    private const DEFAULT_NAMESPACE = 'urn:iso:std:iso:20022:tech:xsd:pain.017.001.04';

    public function __construct(string $namespace = self::DEFAULT_NAMESPACE) {
        parent::__construct($namespace);
    }

    /**
     * Generiert XML aus einem pain.017 Dokument.
     */
    public function generate(Document $document): string {
        $this->initPainDocument('MndtCpyReq');

        $this->addGroupHeader($document);

        foreach ($document->getCopyRequests() as $copyRequest) {
            $this->addCopyRequest($copyRequest);
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

    private function addCopyRequest(MandateCopyRequest $copyRequest): void {
        $this->builder->addElement('UndrlygCpyReqDtls');

        // OrgnlMndt
        $this->builder->addElement('OrgnlMndt');
        $this->builder->addChild('MndtId', $copyRequest->getMandateId());

        if ($copyRequest->getCreditorSchemeId()) {
            $this->addCreditorSchemeId($copyRequest->getCreditorSchemeId());
        }

        $this->builder->end(); // OrgnlMndt

        // InclElctrncSgntr
        if ($copyRequest->includeElectronicSignature() !== null) {
            $this->builder->addChild('InclElctrncSgntr', $copyRequest->includeElectronicSignature() ? 'true' : 'false');
        }

        $this->builder->end(); // UndrlygCpyReqDtls
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
}
