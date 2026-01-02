<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt037Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type37\Document;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;

/**
 * Generator für CAMT.037 XML (Debit Authorisation Request).
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt037Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT037;
    }

    public function generate(Document $document, CamtVersion $version = CamtVersion::V06): string {
        $this->initCamtDocument('DbtAuthstnReq', $version);

        $this->addAssignment($document);
        $this->addCase($document);
        $this->addUnderlying($document);

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
        $this->builder->addElement('Undrlyg');

        if ($document->getOriginalTransactionId() !== null || $document->getOriginalEndToEndId() !== null) {
            $this->builder->addElement('TxId');
            $this->addChildIfNotEmpty('TxId', $document->getOriginalTransactionId());
            $this->addChildIfNotEmpty('EndToEndId', $document->getOriginalEndToEndId());
            $this->builder->end();
        }

        if ($document->getOriginalInterbankSettlementAmount() !== null && $document->getOriginalCurrency() !== null) {
            $this->builder
                ->addElement('IntrBkSttlmAmt', $this->formatAmount($document->getOriginalInterbankSettlementAmount()))
                ->withAttribute('Ccy', $document->getOriginalCurrency()->value)
                ->end();
        }

        if ($document->getOriginalInterbankSettlementDate() !== null) {
            $this->builder->addChild('IntrBkSttlmDt', $this->formatDate($document->getOriginalInterbankSettlementDate()));
        }

        if ($document->getDebtorName() !== null) {
            $this->builder->addElement('Dbtr');
            $this->builder->addChild('Nm', $this->escape($document->getDebtorName()));
            $this->builder->end();

            if ($document->getDebtorAccountIban() !== null) {
                $this->builder->addElement('DbtrAcct')->addElement('Id')
                    ->addChild('IBAN', $this->escape($document->getDebtorAccountIban()))
                    ->end()->end();
            }
        }

        $this->addChildIfNotEmpty('Rsn', $document->getReason());

        $this->builder->end();
    }
}
