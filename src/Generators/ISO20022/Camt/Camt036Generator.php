<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt036Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type36\Document;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;

/**
 * Generator for CAMT.036 XML (Debit Authorisation Response).
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt036Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT036;
    }

    public function generate(Document $document, CamtVersion $version = CamtVersion::V06): string {
        $this->initCamtDocument('DbtAuthstnRspn', $version);

        $this->addAssignment($document);
        $this->addCase($document);
        $this->addConfirmation($document);

        return $this->getXml();
    }

    private function addAssignment(Document $document): void {
        $this->builder->addElement('Assgnmt');
        $this->builder->addChild('Id', $this->escape($document->getAssignmentId()));
        $this->builder->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));

        if ($document->getAssignerAgentBic() !== null || $document->getAssignerPartyName() !== null) {
            $this->builder->addElement('Assgnr');
            if ($document->getAssignerAgentBic() !== null) {
                $this->addAgentByBic('Agt', $document->getAssignerAgentBic());
            } elseif ($document->getAssignerPartyName() !== null) {
                $this->builder->addElement('Pty')->addChild('Nm', $this->escape($document->getAssignerPartyName()))->end();
            }
            $this->builder->end();
        }

        if ($document->getAssigneeAgentBic() !== null || $document->getAssigneePartyName() !== null) {
            $this->builder->addElement('Assgne');
            if ($document->getAssigneeAgentBic() !== null) {
                $this->addAgentByBic('Agt', $document->getAssigneeAgentBic());
            } elseif ($document->getAssigneePartyName() !== null) {
                $this->builder->addElement('Pty')->addChild('Nm', $this->escape($document->getAssigneePartyName()))->end();
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

    private function addConfirmation(Document $document): void {
        $this->builder->addElement('Conf');

        $this->builder->addChild('DbtAuthstn', $document->isDebitAuthorised() ? 'true' : 'false');

        if ($document->getAuthorisedAmount() !== null && $document->getAuthorisedCurrency() !== null) {
            $this->builder->addElement('AmtDtls');
            $this->builder
                ->addElement('InstdAmt', $this->formatAmount($document->getAuthorisedAmount()))
                ->withAttribute('Ccy', $document->getAuthorisedCurrency()->value)
                ->end();
            if ($document->getValueDate() !== null) {
                $this->builder->addChild('ValDt', $this->formatDate($document->getValueDate()));
            }
            $this->builder->end();
        }

        $this->addChildIfNotEmpty('Rsn', $document->getReason());

        $this->builder->end();
    }
}
