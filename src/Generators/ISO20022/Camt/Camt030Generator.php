<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt030Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type30\Document;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;

/**
 * Generator für CAMT.030 XML (Notification of Case Assignment).
 * 
 * Generiert Benachrichtigungen über die Zuweisung eines Falls
 * gemäß ISO 20022 camt.030.001.xx Standard.
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt030Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT030;
    }

    public function generate(Document $document, CamtVersion $version = CamtVersion::V06): string {
        $this->initCamtDocument('NtfctnOfCaseAssgnmt', $version);

        // Hdr (Header)
        $this->addHeader($document);

        // Case
        $this->addCase($document);

        // Assgnmt (Assignment)
        $this->addAssignment($document);

        // Ntfctn (Notification)
        $this->addNotification($document);

        return $this->getXml();
    }

    /**
     * Fügt den Header hinzu.
     */
    private function addHeader(Document $document): void {
        $this->builder->addElement('Hdr');

        $this->builder->addChild('MsgId', $this->escape($document->getHeaderMessageId()));
        $this->builder->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));

        $this->builder->end(); // Hdr
    }

    /**
     * Fügt die Case-Struktur hinzu.
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
     * Fügt die Assignment-Struktur hinzu.
     */
    private function addAssignment(Document $document): void {
        $this->builder->addElement('Assgnmt');

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
     * Fügt die Notification hinzu.
     */
    private function addNotification(Document $document): void {
        if ($document->getNotificationJustification() === null) {
            return;
        }

        $this->builder->addElement('Ntfctn');
        $this->builder->addChild('Justfn', $this->escape($document->getNotificationJustification()));
        $this->builder->end(); // Ntfctn
    }
}
