<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt052Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtDocumentAbstract;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtGeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52\Transaction;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use InvalidArgumentException;

/**
 * Generator für CAMT.052 XML (Bank to Customer Account Report).
 * 
 * Generiert Intraday-Kontoberichte gemäß ISO 20022 camt.052.001.xx Standard.
 * Nutzt ExtendedDOMDocumentBuilder für optimierte XML-Generierung.
 * 
 * @package CommonToolkit\Generators\ISO20022\Camt
 */
class Camt052Generator extends CamtGeneratorAbstract {
    public function getCamtType(): CamtType {
        return CamtType::CAMT052;
    }

    /**
     * @param Document $document
     */
    public function generate(CamtDocumentAbstract $document, CamtVersion $version = CamtVersion::V02): string {
        if (!$document instanceof Document) {
            throw new InvalidArgumentException('Camt052Generator erwartet ein Camt.052 Document.');
        }

        $this->initCamtDocument('BkToCstmrAcctRpt', $version);
        $this->addGroupHeader($document, 'CAMT052');

        // Rpt (Report)
        $this->builder->addElement('Rpt');
        $this->builder->addChild('Id', $this->escape($document->getId()));
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

        $this->builder->end(); // Rpt

        return $this->getXml();
    }

    /**
     * Erstellt ein vollständiges Entry-Element für CAMT.052.
     */
    private function addEntryElement(Transaction $entry): void {
        $this->beginEntry($entry);
        $this->addEntryDates($entry);

        $this->addChildIfNotEmpty('AcctSvcrRef', $entry->getAccountServicerReference());

        // BkTxCd
        if ($entry->getBankTransactionCode() !== null) {
            $this->addBankTxCodeProprietary($entry->getBankTransactionCode());
        }

        // Entry Details
        if ($entry->getPurpose() !== null || $entry->getAdditionalInfo() !== null) {
            $this->builder
                ->addElement('NtryDtls')
                ->addElement('TxDtls');

            if ($entry->getPurpose() !== null) {
                $this->builder
                    ->addElement('Purp')
                    ->addChild('Prtry', $this->escape($entry->getPurpose()))
                    ->end();
            }

            $this->addChildIfNotEmpty('AddtlTxInf', $entry->getAdditionalInfo());

            $this->builder
                ->end() // TxDtls
                ->end(); // NtryDtls
        }

        $this->endEntry();
    }
}
