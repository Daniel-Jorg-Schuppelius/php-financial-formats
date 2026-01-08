<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt087Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type87\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type87\ModificationRequest;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\Camt\CamtVersion;

/**
 * Generator for CAMT.087 XML (Request to Modify Payment).
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt087Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT087;
    }

    public function generate(Document $document, CamtVersion $version = CamtVersion::V09): string {
        $this->initCamtDocument('ReqToModfyPmt', $version);

        $this->addAssignment($document);
        $this->addCase($document);
        $this->addUnderlying($document);
        $this->addModification($document);

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

    private function addUnderlying(Document $document): void {
        if ($document->getOriginalTransactionId() === null && $document->getOriginalEndToEndId() === null) {
            return;
        }

        $this->builder->addElement('Undrlyg');
        $this->builder->addElement('Initn');

        if ($document->getOriginalMessageId() !== null) {
            $this->builder->addElement('OrgnlGrpInf');
            $this->builder->addChild('OrgnlMsgId', $this->escape($document->getOriginalMessageId()));
            $this->addChildIfNotEmpty('OrgnlMsgNmId', $document->getOriginalMessageNameId());
            $this->builder->end();
        }

        $this->addChildIfNotEmpty('OrgnlEndToEndId', $document->getOriginalEndToEndId());
        $this->addChildIfNotEmpty('OrgnlTxId', $document->getOriginalTransactionId());

        if ($document->getOriginalInterbankSettlementAmount() !== null && $document->getOriginalCurrency() !== null) {
            $this->builder
                ->addElement('OrgnlIntrBkSttlmAmt', $this->formatAmount($document->getOriginalInterbankSettlementAmount()))
                ->withAttribute('Ccy', $document->getOriginalCurrency()->value)
                ->end();
        }

        if ($document->getOriginalInterbankSettlementDate() !== null) {
            $this->builder->addChild('OrgnlIntrBkSttlmDt', $this->formatDate($document->getOriginalInterbankSettlementDate()));
        }

        $this->builder->end(); // Initn
        $this->builder->end(); // Undrlyg
    }

    private function addModification(Document $document): void {
        $requests = $document->getModificationRequests();
        if (empty($requests)) {
            return;
        }

        $this->builder->addElement('Mod');

        foreach ($requests as $request) {
            $this->addModificationRequest($request);
        }

        $this->builder->end();
    }

    private function addModificationRequest(ModificationRequest $request): void {
        if ($request->hasAmountModification()) {
            $this->builder->addElement('PmtModDtls');
            $this->builder
                ->addElement('ReqdAmt', $this->formatAmount($request->getRequestedSettlementAmount() ?? 0.0))
                ->withAttribute('Ccy', $request->getRequestedCurrency()?->value ?? 'EUR')
                ->end();
            $this->builder->end();
        }

        if ($request->hasCreditorModification()) {
            $this->builder->addElement('CdtrDtls');
            if ($request->getCreditorName() !== null) {
                $this->builder->addElement('Cdtr')
                    ->addChild('Nm', $this->escape($request->getCreditorName()))
                    ->end();
            }
            if ($request->getCreditorAccount() !== null) {
                $this->builder->addElement('CdtrAcct')->addElement('Id')
                    ->addChild('IBAN', $this->escape($request->getCreditorAccount()))
                    ->end()->end();
            }
            $this->builder->end();
        }

        if ($request->hasDebtorModification()) {
            $this->builder->addElement('DbtrDtls');
            if ($request->getDebtorName() !== null) {
                $this->builder->addElement('Dbtr')
                    ->addChild('Nm', $this->escape($request->getDebtorName()))
                    ->end();
            }
            if ($request->getDebtorAccount() !== null) {
                $this->builder->addElement('DbtrAcct')->addElement('Id')
                    ->addChild('IBAN', $this->escape($request->getDebtorAccount()))
                    ->end()->end();
            }
            $this->builder->end();
        }

        if ($request->getRemittanceInformation() !== null) {
            $this->builder->addElement('RmtInf')
                ->addChild('Ustrd', $this->escape($request->getRemittanceInformation()))
                ->end();
        }

        if ($request->getPurpose() !== null) {
            $this->builder->addElement('Purp')
                ->addChild('Cd', $this->escape($request->getPurpose()))
                ->end();
        }
    }
}
