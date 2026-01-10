<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Transaction.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\TransactionAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\FinancialInstitutionIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\PartyIdentification;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\ReturnReason;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TechnicalInputChannel;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionDomain;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionFamily;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionPurpose;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\TransactionSubFamily;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * CAMT.054 Transaction Entry.
 * 
 * Represents a single booking entry (Ntry) in the
 * Soll/Haben-Benachrichtigung.
 * 
 * CAMT.054 typically contains more details about agents and
 * Referenzen als CAMT.052/053.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Camt054
 */
final class Transaction extends TransactionAbstract {
    private ?string $instructionId;
    private ?string $endToEndId;
    private ?string $remittanceInfo;
    private ?TransactionPurpose $purposeCode;
    private ?string $bankTransactionCode;
    private ?TransactionDomain $domainCode;
    private ?TransactionFamily $familyCode;
    private ?TransactionSubFamily $subFamilyCode;
    private ?ReturnReason $returnReason;
    private ?TechnicalInputChannel $technicalInputChannel;
    private ?string $localInstrumentCode;
    private ?string $instructingAgentBic;
    private ?string $instructedAgentBic;
    private ?string $debtorAgentBic;
    private ?string $creditorAgentBic;

    // Extended party identification
    private ?PartyIdentification $debtor;
    private ?PartyIdentification $creditor;
    private ?FinancialInstitutionIdentification $debtorAgent;
    private ?FinancialInstitutionIdentification $creditorAgent;
    private ?FinancialInstitutionIdentification $instructingAgent;
    private ?FinancialInstitutionIdentification $instructedAgent;

    public function __construct(
        DateTimeImmutable $bookingDate,
        ?DateTimeImmutable $valutaDate,
        float $amount,
        CurrencyCode $currency,
        CreditDebit $creditDebit,
        ?string $entryReference = null,
        ?string $accountServicerReference = null,
        ?string $status = 'BOOK',
        bool $isReversal = false,
        ?string $instructionId = null,
        ?string $endToEndId = null,
        ?string $remittanceInfo = null,
        TransactionPurpose|string|null $purposeCode = null,
        ?string $bankTransactionCode = null,
        TransactionDomain|string|null $domainCode = null,
        TransactionFamily|string|null $familyCode = null,
        TransactionSubFamily|string|null $subFamilyCode = null,
        ReturnReason|string|null $returnReason = null,
        TechnicalInputChannel|string|null $technicalInputChannel = null,
        ?string $localInstrumentCode = null,
        ?string $instructingAgentBic = null,
        ?string $instructedAgentBic = null,
        ?string $debtorAgentBic = null,
        ?string $creditorAgentBic = null,
        ?PartyIdentification $debtor = null,
        ?PartyIdentification $creditor = null,
        ?FinancialInstitutionIdentification $debtorAgent = null,
        ?FinancialInstitutionIdentification $creditorAgent = null,
        ?FinancialInstitutionIdentification $instructingAgent = null,
        ?FinancialInstitutionIdentification $instructedAgent = null
    ) {
        parent::__construct(
            $bookingDate,
            $valutaDate,
            $amount,
            $currency,
            $creditDebit,
            $entryReference,
            $accountServicerReference,
            $status,
            $isReversal
        );

        $this->instructionId = $instructionId;
        $this->endToEndId = $endToEndId;
        $this->remittanceInfo = $remittanceInfo;
        $this->purposeCode = $purposeCode instanceof TransactionPurpose ? $purposeCode : TransactionPurpose::tryFrom($purposeCode ?? '');
        $this->bankTransactionCode = $bankTransactionCode;
        $this->domainCode = $domainCode instanceof TransactionDomain ? $domainCode : TransactionDomain::tryFrom($domainCode ?? '');
        $this->familyCode = $familyCode instanceof TransactionFamily ? $familyCode : TransactionFamily::tryFrom($familyCode ?? '');
        $this->subFamilyCode = $subFamilyCode instanceof TransactionSubFamily ? $subFamilyCode : TransactionSubFamily::tryFrom($subFamilyCode ?? '');
        $this->returnReason = $returnReason instanceof ReturnReason ? $returnReason : ReturnReason::tryFrom($returnReason ?? '');
        $this->technicalInputChannel = $technicalInputChannel instanceof TechnicalInputChannel ? $technicalInputChannel : TechnicalInputChannel::tryFrom($technicalInputChannel ?? '');
        $this->localInstrumentCode = $localInstrumentCode;
        $this->instructingAgentBic = $instructingAgentBic;
        $this->instructedAgentBic = $instructedAgentBic;
        $this->debtorAgentBic = $debtorAgentBic;
        $this->creditorAgentBic = $creditorAgentBic;
        $this->debtor = $debtor;
        $this->creditor = $creditor;
        $this->debtorAgent = $debtorAgent;
        $this->creditorAgent = $creditorAgent;
        $this->instructingAgent = $instructingAgent;
        $this->instructedAgent = $instructedAgent;
    }

    public function getInstructionId(): ?string {
        return $this->instructionId;
    }

    public function getEndToEndId(): ?string {
        return $this->endToEndId;
    }

    public function getRemittanceInfo(): ?string {
        return $this->remittanceInfo;
    }

    public function getPurposeCode(): ?TransactionPurpose {
        return $this->purposeCode;
    }

    public function getReturnReason(): ?ReturnReason {
        return $this->returnReason;
    }

    public function getTechnicalInputChannel(): ?TechnicalInputChannel {
        return $this->technicalInputChannel;
    }

    public function getBankTransactionCode(): ?string {
        return $this->bankTransactionCode;
    }

    public function getDomainCode(): ?TransactionDomain {
        return $this->domainCode;
    }

    public function getFamilyCode(): ?TransactionFamily {
        return $this->familyCode;
    }

    public function getSubFamilyCode(): ?TransactionSubFamily {
        return $this->subFamilyCode;
    }

    /**
     * Returns the complete transaction code (Domain/Family/SubFamily).
     */
    public function getFullTransactionCode(): ?string {
        if ($this->domainCode === null) {
            return $this->bankTransactionCode;
        }

        $code = $this->domainCode->value;
        if ($this->familyCode !== null) {
            $code .= '/' . $this->familyCode->value;
            if ($this->subFamilyCode !== null) {
                $code .= '/' . $this->subFamilyCode->value;
            }
        }

        return $code;
    }

    public function getLocalInstrumentCode(): ?string {
        return $this->localInstrumentCode;
    }

    public function getInstructingAgentBic(): ?string {
        return $this->instructingAgentBic;
    }

    public function getInstructedAgentBic(): ?string {
        return $this->instructedAgentBic;
    }

    public function getDebtorAgentBic(): ?string {
        return $this->debtorAgentBic;
    }

    public function getCreditorAgentBic(): ?string {
        return $this->creditorAgentBic;
    }

    public function getDebtor(): ?PartyIdentification {
        return $this->debtor;
    }

    public function getCreditor(): ?PartyIdentification {
        return $this->creditor;
    }

    public function getDebtorAgent(): ?FinancialInstitutionIdentification {
        return $this->debtorAgent;
    }

    public function getCreditorAgent(): ?FinancialInstitutionIdentification {
        return $this->creditorAgent;
    }

    public function getInstructingAgent(): ?FinancialInstitutionIdentification {
        return $this->instructingAgent;
    }

    public function getInstructedAgent(): ?FinancialInstitutionIdentification {
        return $this->instructedAgent;
    }

    /**
     * Creates a summary description of the transaction.
     */
    public function getSummary(): string {
        $parts = [];

        $parts[] = $this->bookingDate->format('d.m.Y H:i:s');
        $parts[] = ($this->isCredit() ? '+' : '-') . number_format($this->amount, 2, ',', '.') . ' ' . $this->currency->value;

        if ($this->endToEndId !== null) {
            $parts[] = 'E2E: ' . $this->endToEndId;
        }

        if ($this->remittanceInfo !== null) {
            $parts[] = $this->remittanceInfo;
        }

        return implode(' | ', $parts);
    }

    /**
     * Returns a string representation of the transaction.
     */
    public function __toString(): string {
        return $this->getSummary();
    }
}
