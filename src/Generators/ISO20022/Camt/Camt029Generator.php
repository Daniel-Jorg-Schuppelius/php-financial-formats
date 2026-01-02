<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt029Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtDocumentAbstract;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\CancellationDetails;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type29\TransactionInformationAndStatus;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use InvalidArgumentException;

/**
 * Generator für CAMT.029 XML (Resolution of Investigation).
 * 
 * Generiert Antworten auf Untersuchungsanfragen gemäß ISO 20022 camt.029.001.xx Standard.
 * Nutzt ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt029Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT029;
    }

    /**
     * @param Document $document
     */
    public function generate(CamtDocumentAbstract $document, CamtVersion $version = CamtVersion::V13): string {
        if (!$document instanceof Document) {
            throw new InvalidArgumentException('Camt029Generator erwartet ein Camt.029 Document.');
        }

        $this->initCamtDocument('RsltnOfInvstgtn', $version);

        // Assgnmt (Assignment)
        $this->addAssignment($document);

        // Case (optional)
        $this->addCase($document);

        // Sts (Status)
        $this->addStatus($document);

        // CxlDtls (Cancellation Details)
        foreach ($document->getCancellationDetails() as $details) {
            $this->addCancellationDetails($details);
        }

        return $this->getXml();
    }

    /**
     * Fügt die Assignment-Struktur hinzu.
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
     * Fügt die Case-Struktur hinzu (optional).
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
     * Fügt die Status-Struktur hinzu.
     */
    private function addStatus(Document $document): void {
        if ($document->getInvestigationStatus() === null && $document->getInvestigationStatusProprietary() === null) {
            return;
        }

        $this->builder->addElement('Sts');

        if ($document->getInvestigationStatus() !== null) {
            $this->builder->addChild('Conf', $this->escape($document->getInvestigationStatus()));
        } else {
            $this->builder
                ->addElement('Prtry')
                ->addChild('Id', $this->escape($document->getInvestigationStatusProprietary()))
                ->end();
        }

        $this->builder->end(); // Sts
    }

    /**
     * Fügt Cancellation Details hinzu.
     */
    private function addCancellationDetails(CancellationDetails $details): void {
        $this->builder->addElement('CxlDtls');

        // OrgnlGrpInfAndSts
        $grpInfAndSts = $details->getOriginalGroupInformationAndStatus();
        if ($grpInfAndSts !== null && $grpInfAndSts->getOriginalMessageId() !== null) {
            $this->builder->addElement('OrgnlGrpInfAndSts');
            $this->builder->addChild('OrgnlMsgId', $this->escape($grpInfAndSts->getOriginalMessageId()));

            $this->addChildIfNotEmpty('OrgnlMsgNmId', $grpInfAndSts->getOriginalMessageNameId());

            if ($grpInfAndSts->getOriginalCreationDateTime() !== null) {
                $this->builder->addChild('OrgnlCreDtTm', $this->formatDateTime($grpInfAndSts->getOriginalCreationDateTime()));
            }

            $this->addChildIfNotEmpty('GrpCxlSts', $grpInfAndSts->getGroupCancellationStatus());

            $this->builder->end(); // OrgnlGrpInfAndSts
        }

        // TxInfAndSts
        foreach ($details->getTransactionInformationAndStatus() as $txInfAndSts) {
            $this->addTransactionInformationAndStatus($txInfAndSts);
        }

        $this->builder->end(); // CxlDtls
    }

    /**
     * Fügt Transaction Information and Status hinzu.
     */
    private function addTransactionInformationAndStatus(TransactionInformationAndStatus $txInfAndSts): void {
        $this->builder->addElement('TxInfAndSts');

        $this->addChildIfNotEmpty('CxlStsId', $txInfAndSts->getCancellationStatusId());
        $this->addChildIfNotEmpty('OrgnlEndToEndId', $txInfAndSts->getOriginalEndToEndId());
        $this->addChildIfNotEmpty('OrgnlTxId', $txInfAndSts->getOriginalTransactionId());
        $this->addChildIfNotEmpty('TxCxlSts', $txInfAndSts->getTransactionCancellationStatus());

        // CxlStsRsnInf
        foreach ($txInfAndSts->getCancellationStatusReasonInformation() as $stsRsn) {
            $this->builder->addElement('CxlStsRsnInf');

            $this->builder->addElement('Rsn');
            if ($stsRsn->getStatusCode() !== null) {
                $this->builder->addChild('Cd', $this->escape($stsRsn->getStatusCode()));
            } elseif ($stsRsn->getStatusProprietary() !== null) {
                $this->builder->addChild('Prtry', $this->escape($stsRsn->getStatusProprietary()));
            }
            $this->builder->end(); // Rsn

            $this->addChildIfNotEmpty('AddtlInf', $stsRsn->getAdditionalInformation());

            $this->builder->end(); // CxlStsRsnInf
        }

        $this->builder->end(); // TxInfAndSts
    }
}
