<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt103Generator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Generators\Mt;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt1\GeneratorAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt1\Type103\Document;

/**
 * Generator for MT103 Single Customer Credit Transfer.
 * 
 * Generates SWIFT MT103 messages from Document objects.
 * 
 * @package CommonToolkit\Generators\Common\Banking\Mt
 */
class Mt103Generator extends GeneratorAbstract {
    /**
     * Generates the MT103 SWIFT message.
     * 
     * @param object $document Das MT103-Dokument
     * @return string Die formatierte SWIFT-Nachricht
     */
    public function generate(object $document): string {
        if (!$document instanceof Document) {
            throw new \InvalidArgumentException('Expected Mt1\Type103\Document');
        }

        $lines = [];

        // Pflichtfelder
        $lines[] = ':20:' . $document->getSendersReference();
        $lines[] = ':23B:' . $document->getBankOperationCode()->value;

        // Transaction Type Code (:26T:) - optional
        if ($document->getTransactionTypeCode() !== null) {
            $lines[] = ':26T:' . $document->getTransactionTypeCode();
        }

        // Value Date, Currency, Amount (:32A:)
        $lines[] = ':32A:' . $document->getTransferDetails()->toField32A();

        // Instructed Amount bei Währungsumrechnung (:33B:, :36:)
        $this->appendCurrencyConversion($lines, $document);

        // Ordering Customer (:50K:)
        $lines[] = $this->formatPartyOptionK(':50K:', $document->getOrderingCustomer());

        // Ordering Institution (:52A: / :52D:) - optional
        $this->appendOptionalParty($lines, $document->getOrderingInstitution(), ':52A:', ':52D:');

        // Sender's Correspondent (:53A: / :53B:) - optional
        $this->appendOptionalParty($lines, $document->getSendersCorrespondent(), ':53A:', ':53B:');

        // Intermediary Institution (:56A: / :56D:) - optional
        $this->appendOptionalParty($lines, $document->getIntermediaryInstitution(), ':56A:', ':56D:');

        // Account With Institution (:57A: / :57D:) - optional
        $this->appendOptionalParty($lines, $document->getAccountWithInstitution(), ':57A:', ':57D:');

        // Beneficiary (:59:)
        $lines[] = ':59:' . $document->getBeneficiary()->toOptionK();

        // Remittance Information (:70:) - optional
        if ($document->getRemittanceInfo() !== null) {
            $lines[] = ':70:' . $document->getRemittanceInfo();
        }

        // Details of Charges (:71A:) - optional
        if ($document->getChargesCode() !== null) {
            $lines[] = ':71A:' . $document->getChargesCode()->value;
        }

        // Sender to Receiver Information (:72:) - optional
        if ($document->getSenderToReceiverInfo() !== null) {
            $lines[] = ':72:' . $document->getSenderToReceiverInfo();
        }

        // Regulatory Reporting (:77B:) - optional
        if ($document->getRegulatoryReporting() !== null) {
            $lines[] = ':77B:' . $document->getRegulatoryReporting();
        }

        return $this->joinLines($lines);
    }

    /**
     * Adds the currency conversion fields if present.
     * 
     * @param string[] $lines Referenz auf das Array der Zeilen
     * @param Document $document Das Dokument
     */
    private function appendCurrencyConversion(array &$lines, Document $document): void {
        $transferDetails = $document->getTransferDetails();

        if (!$transferDetails->hasCurrencyConversion()) {
            return;
        }

        $originalCurrency = $transferDetails->getOriginalCurrency();
        $originalAmount = $transferDetails->getOriginalAmount();

        if ($originalCurrency !== null && $originalAmount !== null) {
            $lines[] = ':33B:' . $originalCurrency->value .
                str_replace('.', ',', number_format($originalAmount, 2, '.', ''));
        }

        $exchangeRate = $transferDetails->getExchangeRate();
        if ($exchangeRate !== null) {
            $lines[] = ':36:' . number_format($exchangeRate, 6, ',', '');
        }
    }
}
