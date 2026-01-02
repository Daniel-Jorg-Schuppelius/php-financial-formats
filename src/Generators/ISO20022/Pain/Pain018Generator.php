<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain018Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain\PainGeneratorAbstract;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type18\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type18\MandateSuspensionRequest;

/**
 * Generiert pain.018 XML-Dokumente (Mandate Suspension Request).
 * 
 * Verwendet ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * Unterstützt pain.018.001.04 (ISO 20022).
 * 
 * @package CommonToolkit\Generators\ISO20022\Pain
 */
class Pain018Generator extends PainGeneratorAbstract {
    private const DEFAULT_NAMESPACE = 'urn:iso:std:iso:20022:tech:xsd:pain.018.001.04';

    public function __construct(string $namespace = self::DEFAULT_NAMESPACE) {
        parent::__construct($namespace);
    }

    /**
     * Generiert XML aus einem pain.018 Dokument.
     */
    public function generate(Document $document): string {
        $this->initPainDocument('MndtSspnsnReq');

        $this->addGroupHeader($document);

        foreach ($document->getSuspensionRequests() as $suspensionRequest) {
            $this->addSuspensionRequest($suspensionRequest);
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

    private function addSuspensionRequest(MandateSuspensionRequest $request): void {
        $this->builder->addElement('UndrlygSspnsnDtls');

        // OrgnlMndt
        $this->builder->addElement('OrgnlMndt');
        $this->builder->addChild('MndtId', $request->getMandateId());

        if ($request->getCreditorSchemeId()) {
            $this->addCreditorSchemeId($request->getCreditorSchemeId());
        }

        $this->builder->end(); // OrgnlMndt

        // SspnsnPrd
        $this->builder
            ->addElement('SspnsnPrd')
            ->addChild('FrDt', $this->formatDate($request->getSuspensionStartDate()))
            ->addChild('ToDt', $this->formatDate($request->getSuspensionEndDate()))
            ->end(); // SspnsnPrd

        // SspnsnRsn
        if ($request->getSuspensionReason()) {
            $this->builder
                ->addElement('SspnsnRsn')
                ->addElement('Rsn')
                ->addChild('Prtry', $request->getSuspensionReason())
                ->end() // Rsn
                ->end(); // SspnsnRsn
        }

        $this->builder->end(); // UndrlygSspnsnDtls
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
