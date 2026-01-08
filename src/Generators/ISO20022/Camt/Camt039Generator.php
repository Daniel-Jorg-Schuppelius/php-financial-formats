<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt039Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type39\Document;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtVersion;

/**
 * Generator for CAMT.039 XML (Case Status Report).
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt039Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT039;
    }

    public function generate(Document $document, CamtVersion $version = CamtVersion::V06): string {
        $this->initCamtDocument('CaseStsRpt', $version);

        $this->addReportHeader($document);
        $this->addCase($document);
        $this->addStatus($document);

        return $this->getXml();
    }

    private function addReportHeader(Document $document): void {
        $this->builder->addElement('Hdr');
        $this->builder->addChild('Id', $this->escape($document->getReportId()));
        $this->builder->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));

        if ($document->getReporterAgentBic() !== null || $document->getReporterPartyName() !== null) {
            $this->builder->addElement('Rprtr');
            if ($document->getReporterAgentBic() !== null) {
                $this->addAgentByBic('Agt', $document->getReporterAgentBic());
            } elseif ($document->getReporterPartyName() !== null) {
                $this->builder->addElement('Pty')->addChild('Nm', $this->escape($document->getReporterPartyName()))->end();
            }
            $this->builder->end();
        }

        if ($document->getReceiverAgentBic() !== null || $document->getReceiverPartyName() !== null) {
            $this->builder->addElement('Rcvr');
            if ($document->getReceiverAgentBic() !== null) {
                $this->addAgentByBic('Agt', $document->getReceiverAgentBic());
            } elseif ($document->getReceiverPartyName() !== null) {
                $this->builder->addElement('Pty')->addChild('Nm', $this->escape($document->getReceiverPartyName()))->end();
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

    private function addStatus(Document $document): void {
        $this->builder->addElement('Sts');

        $this->addChildIfNotEmpty('Cd', $document->getStatusCode());
        $this->addChildIfNotEmpty('Rsn', $document->getStatusReason());
        $this->addChildIfNotEmpty('AddtlInf', $document->getAdditionalInformation());

        $this->builder->end();
    }
}
