<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt031Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type31\Document;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;

/**
 * Generator for CAMT.031 XML (Reject Investigation).
 * 
 * Generates rejections of investigation requests
 * according to ISO 20022 camt.031.001.xx Standard.
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt031Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT031;
    }

    public function generate(Document $document, CamtVersion $version = CamtVersion::V06): string {
        $this->initCamtDocument('RjctInvstgtn', $version);

        // Assgnmt (Assignment)
        $this->addAssignment($document);

        // Case
        $this->addCase($document);

        // Justfn (Justification)
        $this->addJustification($document);

        return $this->getXml();
    }

    /**
     * Adds the assignment structure.
     */
    private function addAssignment(Document $document): void {
        $this->builder->addElement('Assgnmt');

        $this->builder->addChild('Id', $this->escape($document->getAssignmentId()));
        $this->builder->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));

        // Assigner
        if ($document->getAssignerAgentBic() !== null || $document->getAssignerPartyName() !== null) {
            $this->builder->addElement('Assgnr');

            if ($document->getAssignerAgentBic() !== null) {
                $this->addAgentByBic('Agt', $document->getAssignerAgentBic());
            } elseif ($document->getAssignerPartyName() !== null) {
                $this->builder
                    ->addElement('Pty')
                    ->addChild('Nm', $this->escape($document->getAssignerPartyName()))
                    ->end();
            }

            $this->builder->end(); // Assgnr
        }

        // Assignee
        if ($document->getAssigneeAgentBic() !== null || $document->getAssigneePartyName() !== null) {
            $this->builder->addElement('Assgne');

            if ($document->getAssigneeAgentBic() !== null) {
                $this->addAgentByBic('Agt', $document->getAssigneeAgentBic());
            } elseif ($document->getAssigneePartyName() !== null) {
                $this->builder
                    ->addElement('Pty')
                    ->addChild('Nm', $this->escape($document->getAssigneePartyName()))
                    ->end();
            }

            $this->builder->end(); // Assgne
        }

        $this->builder->end(); // Assgnmt
    }

    /**
     * Adds the case structure.
     */
    private function addCase(Document $document): void {
        if ($document->getCaseId() === null) {
            return;
        }

        $this->builder->addElement('Case');
        $this->builder->addChild('Id', $this->escape($document->getCaseId()));

        if ($document->getCaseCreator() !== null) {
            $this->builder
                ->addElement('Cretr')
                ->addElement('Pty')
                ->addChild('Nm', $this->escape($document->getCaseCreator()))
                ->end() // Pty
                ->end(); // Cretr
        }

        $this->builder->end(); // Case
    }

    /**
     * Adds the justification (rejection reason).
     */
    private function addJustification(Document $document): void {
        if ($document->getRejectionReasonCode() === null && $document->getRejectionReasonProprietary() === null) {
            return;
        }

        $this->builder->addElement('Justfn');
        $this->builder->addElement('RjctnRsn');

        if ($document->getRejectionReasonCode() !== null) {
            $this->builder->addChild('Cd', $this->escape($document->getRejectionReasonCode()));
        } elseif ($document->getRejectionReasonProprietary() !== null) {
            $this->builder->addChild('Prtry', $this->escape($document->getRejectionReasonProprietary()));
        }

        $this->builder->end(); // RjctnRsn

        $this->addChildIfNotEmpty('AddtlInf', $document->getAdditionalInformation());

        $this->builder->end(); // Justfn
    }
}
