<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt054Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtDocumentAbstract;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54\Transaction;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;
use CommonToolkit\Helper\Data\BankHelper;
use InvalidArgumentException;

/**
 * Generator for CAMT.054 XML (Bank to Customer Debit Credit Notification).
 * 
 * Generates Debit/Credit notification according to ISO 20022 camt.054.001.xx Standard.
 * Uses ExtendedDOMDocumentBuilder for optimized XML generation.
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt054Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT054;
    }

    /**
     * @param Document $document
     */
    public function generate(CamtDocumentAbstract $document, CamtVersion $version = CamtVersion::V02): string {
        if (!$document instanceof Document) {
            throw new InvalidArgumentException('Camt054Generator erwartet ein Camt.054 Document.');
        }

        $this->initCamtDocument('BkToCstmrDbtCdtNtfctn', $version);
        $this->addGroupHeader($document, 'CAMT054');

        // Ntfctn (Notification)
        $this->builder->addElement('Ntfctn');
        $this->builder->addChild('Id', $this->escape($document->getId()));

        // Account (spezielle Struktur für CAMT.054)
        $this->addAccountForNotification($document);

        // Entries
        foreach ($document->getEntries() as $entry) {
            $this->addEntryElement($entry);
        }

        $this->builder->end(); // Ntfctn

        return $this->getXml();
    }

    /**
     * Adds the account structure for CAMT.054 (different from 052/053).
     */
    private function addAccountForNotification(CamtDocumentAbstract $document): void {
        $this->builder->addElement('Acct');

        // Account ID
        $this->builder->addElement('Id');
        if (BankHelper::shouldFormatAsIBAN($document->getAccountIdentifier())) {
            $this->builder->addChild('IBAN', $this->escape($document->getAccountIdentifier()));
        } else {
            $this->builder
                ->addElement('Othr')
                ->addChild('Id', $this->escape($document->getAccountIdentifier()))
                ->end();
        }
        $this->builder->end(); // Id

        // Owner (mit OrgId/AnyBIC für CAMT.054)
        if ($document->getAccountOwner() !== null) {
            $this->builder
                ->addElement('Ownr')
                ->addElement('Id')
                ->addElement('OrgId')
                ->addChild('AnyBIC', $this->escape($document->getAccountOwner()))
                ->end() // OrgId
                ->end() // Id
                ->end(); // Ownr
        }

        $this->builder->end(); // Acct
    }

    /**
     * Creates a complete entry element for CAMT.054.
     */
    private function addEntryElement(Transaction $entry): void {
        // Basis-Entry mit Status, Amount, CreditDebit
        $this->beginEntry($entry);

        // CAMT.054 typically uses DtTm instead of Dt
        $this->addEntryDates($entry, true);

        // BkTxCd
        if ($entry->getBankTransactionCode() !== null) {
            $this->addBankTxCodeProprietary($entry->getBankTransactionCode());
        }

        // Entry Details
        $this->builder
            ->addElement('NtryDtls')
            ->addElement('TxDtls');

        // Refs
        if ($entry->getInstructionId() !== null || $entry->getEndToEndId() !== null) {
            $this->builder->addElement('Refs');
            $this->addChildIfNotEmpty('InstrId', $entry->getInstructionId());
            $this->addChildIfNotEmpty('EndToEndId', $entry->getEndToEndId());
            $this->builder->end(); // Refs
        }

        // Amount in TxDtls
        $this->addAmount('Amt', $entry->getAmount(), $entry->getCurrency());

        // Related Agents
        $this->addRelatedAgents($entry);

        // Remittance Info
        if ($entry->getRemittanceInfo() !== null) {
            $this->builder
                ->addElement('RmtInf')
                ->addChild('Ustrd', $this->escape($entry->getRemittanceInfo()))
                ->end();
        }

        $this->builder
            ->end() // TxDtls
            ->end(); // NtryDtls

        $this->endEntry();
    }

    /**
     * Adds related agents to the transaction.
     */
    private function addRelatedAgents(Transaction $entry): void {
        if ($entry->getInstructingAgentBic() === null && $entry->getDebtorAgentBic() === null) {
            return;
        }

        $this->builder->addElement('RltdAgts');

        if ($entry->getInstructingAgentBic() !== null) {
            $this->addAgentByBic('InstgAgt', $entry->getInstructingAgentBic());
        }

        if ($entry->getDebtorAgentBic() !== null) {
            $this->addAgentByBic('DbtrAgt', $entry->getDebtorAgentBic());
        }

        $this->builder->end(); // RltdAgts
    }
}
