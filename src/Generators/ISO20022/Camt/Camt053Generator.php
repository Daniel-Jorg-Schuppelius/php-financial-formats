<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt053Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\DocumentAbstract;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\GeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Reference;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Transaction;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;
use InvalidArgumentException;

/**
 * Generator for CAMT.053 XML (Bank to Customer Statement).
 * 
 * Generates daily statements according to ISO 20022 camt.053.001.xx standard.
 * Uses ExtendedDOMDocumentBuilder for optimized XML generation.
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt053Generator extends GeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT053;
    }

    /**
     * @param Document $document
     */
    public function generate(DocumentAbstract $document, CamtVersion $version = CamtVersion::V02): string {
        if (!$document instanceof Document) {
            throw new InvalidArgumentException('Camt053Generator erwartet ein Camt.053 Document.');
        }

        $this->initCamtDocument('BkToCstmrStmt', $version);
        $this->addGroupHeader($document, 'CAMT053');

        // Stmt (Statement)
        $this->builder->addElement('Stmt');
        $this->builder->addChild('Id', $this->escape($document->getId()));

        // Statement Pagination
        $this->addStatementPagination($document);

        $this->addChildIfNotEmpty('ElctrncSeqNb', $document->getSequenceNumber());
        $this->builder->addChild('CreDtTm', $this->formatDateTime($document->getCreationDateTime()));

        // Account
        $this->addCamtAccountFull($document);

        // Balances
        if ($document->getOpeningBalance() !== null) {
            $this->addBalance($document->getOpeningBalance());
        }
        if ($document->getClosingBalance() !== null) {
            $this->addBalance($document->getClosingBalance());
        }

        // Entries
        foreach ($document->getEntries() as $entry) {
            $this->addEntryElement($entry);
        }

        $this->builder->end(); // Stmt

        return $this->getXml();
    }

    /**
     * Creates a complete entry element for CAMT.053.
     */
    private function addEntryElement(Transaction $entry): void {
        $this->beginEntry($entry);
        $this->addEntryDates($entry);

        $this->addChildIfNotEmpty('AcctSvcrRef', $entry->getAccountServicerReference());

        // BkTxCd
        if ($entry->getTransactionCode() !== null) {
            $this->addBankTxCodeProprietary($entry->getTransactionCode());
        }

        // Entry Details
        $this->builder
            ->addElement('NtryDtls')
            ->addElement('TxDtls');

        // References
        $reference = $entry->getReference();
        if ($reference->hasAnyReference()) {
            $this->addReferences($reference);
        }

        // Related Parties (Counterparty)
        $this->addRelatedParties($entry);

        // Remittance Information
        if ($entry->getPurpose() !== null) {
            $this->builder
                ->addElement('RmtInf')
                ->addChild('Ustrd', $this->escape($entry->getPurpose()))
                ->end();
        }

        $this->builder
            ->end() // TxDtls
            ->end(); // NtryDtls

        // Additional Entry Info
        $this->addChildIfNotEmpty('AddtlNtryInf', $entry->getAdditionalInfo());

        $this->endEntry();
    }

    /**
     * Adds references to the transaction.
     */
    private function addReferences(Reference $reference): void {
        $this->builder->addElement('Refs');

        $this->addChildIfNotEmpty('EndToEndId', $reference->getEndToEndId());
        $this->addChildIfNotEmpty('MndtId', $reference->getMandateId());
        $this->addChildIfNotEmpty('CdtrId', $reference->getCreditorId());
        $this->addChildIfNotEmpty('PmtInfId', $reference->getPaymentInformationId());
        $this->addChildIfNotEmpty('InstrId', $reference->getInstructionId());

        $this->builder->end(); // Refs
    }

    /**
     * Adds related parties to the transaction.
     */
    private function addRelatedParties(Transaction $entry): void {
        $counterpartyName = $entry->getCounterpartyName();
        $counterpartyIban = $entry->getCounterpartyIban();

        if ($counterpartyName === null && $counterpartyIban === null) {
            return;
        }

        $this->builder->addElement('RltdPties');

        $partyElement = $entry->isCredit() ? 'Dbtr' : 'Cdtr';
        $this->builder->addElement($partyElement);

        if ($counterpartyName !== null) {
            $this->builder->addChild('Nm', $this->escape($counterpartyName));
        }

        $this->builder->end(); // party element

        if ($counterpartyIban !== null) {
            $acctElement = $entry->isCredit() ? 'DbtrAcct' : 'CdtrAcct';
            $this->builder
                ->addElement($acctElement)
                ->addElement('Id')
                ->addChild('IBAN', $this->escape($counterpartyIban))
                ->end() // Id
                ->end(); // AcctElement
        }

        if ($entry->getCounterpartyBic() !== null) {
            $agentElement = $entry->isCredit() ? 'DbtrAgt' : 'CdtrAgt';
            $this->addAgentByBic($agentElement, $entry->getCounterpartyBic());
        }

        $this->builder->end(); // RltdPties
    }
}
