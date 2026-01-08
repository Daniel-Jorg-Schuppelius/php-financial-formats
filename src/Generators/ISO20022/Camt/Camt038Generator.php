<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt038Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type38\Document;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtVersion;

/**
 * Generator for CAMT.038 XML (Case Status Report Request).
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt038Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT038;
    }

    public function generate(Document $document, CamtVersion $version = CamtVersion::V06): string {
        $this->initCamtDocument('CaseStsRptReq', $version);

        $this->addRequestHeader($document);
        $this->addCase($document);

        return $this->getXml();
    }

    private function addRequestHeader(Document $document): void {
        $this->builder->addElement('ReqHdr');
        $this->builder->addChild('Id', $this->escape($document->getRequestId()));
        $this->builder->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));

        if ($document->getRequesterAgentBic() !== null || $document->getRequesterPartyName() !== null) {
            $this->builder->addElement('Reqstr');
            if ($document->getRequesterAgentBic() !== null) {
                $this->addAgentByBic('Agt', $document->getRequesterAgentBic());
            } elseif ($document->getRequesterPartyName() !== null) {
                $this->builder->addElement('Pty')->addChild('Nm', $this->escape($document->getRequesterPartyName()))->end();
            }
            $this->builder->end();
        }

        if ($document->getResponderAgentBic() !== null || $document->getResponderPartyName() !== null) {
            $this->builder->addElement('Rspndr');
            if ($document->getResponderAgentBic() !== null) {
                $this->addAgentByBic('Agt', $document->getResponderAgentBic());
            } elseif ($document->getResponderPartyName() !== null) {
                $this->builder->addElement('Pty')->addChild('Nm', $this->escape($document->getResponderPartyName()))->end();
            }
            $this->builder->end();
        }

        $this->builder->end();
    }

    private function addCase(Document $document): void {
        if ($document->getCaseId() === null) {
            return;
        }

        $this->builder->addElement('Case');
        $this->builder->addChild('Id', $this->escape($document->getCaseId()));
        if ($document->getCaseCreator() !== null) {
            $this->builder->addElement('Cretr')->addElement('Pty')
                ->addChild('Nm', $this->escape($document->getCaseCreator()))
                ->end()->end();
        }
        $this->builder->end();
    }
}
