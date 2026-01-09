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

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtTransactionAbstract;
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
 * CAMT.052 Transaction Entry.
 * 
 * Represents a single booking entry (Ntry) in
 * intraday account report.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Camt052
 */
final class Transaction extends CamtTransactionAbstract {
    private ?string $purpose;
    private ?TransactionPurpose $purposeCode;
    private ?string $additionalInfo;
    private ?string $bankTransactionCode;
    private ?TransactionDomain $domainCode;
    private ?TransactionFamily $familyCode;
    private ?TransactionSubFamily $subFamilyCode;
    private ?ReturnReason $returnReason;
    private ?TechnicalInputChannel $technicalInputChannel;
    private ?string $counterpartyName;
    private ?string $counterpartyIban;
    private ?string $counterpartyBic;
    private ?string $remittanceInfo;

    // Extended party identification
    private ?PartyIdentification $debtor;
    private ?PartyIdentification $creditor;
    private ?FinancialInstitutionIdentification $debtorAgent;
    private ?FinancialInstitutionIdentification $creditorAgent;

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
        ?string $purpose = null,
        TransactionPurpose|string|null $purposeCode = null,
        ?string $additionalInfo = null,
        ?string $bankTransactionCode = null,
        TransactionDomain|string|null $domainCode = null,
        TransactionFamily|string|null $familyCode = null,
        TransactionSubFamily|string|null $subFamilyCode = null,
        ReturnReason|string|null $returnReason = null,
        TechnicalInputChannel|string|null $technicalInputChannel = null,
        ?string $counterpartyName = null,
        ?string $counterpartyIban = null,
        ?string $counterpartyBic = null,
        ?string $remittanceInfo = null,
        ?PartyIdentification $debtor = null,
        ?PartyIdentification $creditor = null,
        ?FinancialInstitutionIdentification $debtorAgent = null,
        ?FinancialInstitutionIdentification $creditorAgent = null
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

        $this->purpose = $purpose;
        $this->purposeCode = $purposeCode instanceof TransactionPurpose ? $purposeCode : TransactionPurpose::tryFrom($purposeCode ?? '');
        $this->additionalInfo = $additionalInfo;
        $this->bankTransactionCode = $bankTransactionCode;
        $this->domainCode = $domainCode instanceof TransactionDomain ? $domainCode : TransactionDomain::tryFrom($domainCode ?? '');
        $this->familyCode = $familyCode instanceof TransactionFamily ? $familyCode : TransactionFamily::tryFrom($familyCode ?? '');
        $this->subFamilyCode = $subFamilyCode instanceof TransactionSubFamily ? $subFamilyCode : TransactionSubFamily::tryFrom($subFamilyCode ?? '');
        $this->returnReason = $returnReason instanceof ReturnReason ? $returnReason : ReturnReason::tryFrom($returnReason ?? '');
        $this->technicalInputChannel = $technicalInputChannel instanceof TechnicalInputChannel ? $technicalInputChannel : TechnicalInputChannel::tryFrom($technicalInputChannel ?? '');
        $this->counterpartyName = $counterpartyName;
        $this->counterpartyIban = $counterpartyIban;
        $this->counterpartyBic = $counterpartyBic;
        $this->remittanceInfo = $remittanceInfo;
        $this->debtor = $debtor;
        $this->creditor = $creditor;
        $this->debtorAgent = $debtorAgent;
        $this->creditorAgent = $creditorAgent;
    }

    public function getPurpose(): ?string {
        return $this->purpose;
    }

    public function getPurposeCode(): ?TransactionPurpose {
        return $this->purposeCode;
    }

    public function getAdditionalInfo(): ?string {
        return $this->additionalInfo;
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

    public function getCounterpartyName(): ?string {
        return $this->counterpartyName;
    }

    public function getCounterpartyIban(): ?string {
        return $this->counterpartyIban;
    }

    public function getCounterpartyBic(): ?string {
        return $this->counterpartyBic;
    }

    public function getRemittanceInfo(): ?string {
        return $this->remittanceInfo;
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

    /**
     * Creates a summary description of the transaction.
     */
    public function getSummary(): string {
        $parts = [];

        $parts[] = $this->bookingDate->format('d.m.Y');
        $parts[] = ($this->isCredit() ? '+' : '-') . number_format($this->amount, 2, ',', '.') . ' ' . $this->currency->value;

        if ($this->purpose !== null) {
            $parts[] = $this->purpose;
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
