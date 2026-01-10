<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt034Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\GeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type34\Document;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;

/**
 * Generator for CAMT.034 XML (Duplicate).
 * 
 * Generates responses to duplicate requests
 * according to ISO 20022 camt.034.001.xx Standard.
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt034Generator extends GeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT034;
    }

    public function generate(Document $document, CamtVersion $version = CamtVersion::V06): string {
        $this->initCamtDocument('Dplct', $version);

        // Assgnmt (Assignment)
        $this->addAssignment($document);

        // Case
        $this->addCase($document);

        // Dplct (Duplicate Content)
        $this->addDuplicate($document);

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
     * Adds the duplicate content.
     */
    private function addDuplicate(Document $document): void {
        if ($document->getDuplicateContent() === null) {
            return;
        }

        $this->builder->addElement('Dplct');

        if ($document->getDuplicateContentType() !== null) {
            $this->builder->addChild('Tp', $this->escape($document->getDuplicateContentType()));
        }

        // Content is already base64-encoded in the entity
        $this->builder->addChild('Dplct', $document->getDuplicateContent());

        $this->builder->end(); // Dplct
    }
}
