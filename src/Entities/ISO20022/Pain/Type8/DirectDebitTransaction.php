<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DirectDebitTransaction.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PaymentIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\RemittanceInformation;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * Direct Debit Transaction Information für pain.008 (DrctDbtTxInf).
 * 
 * Einzelne Lastschrift-Transaktion mit Debtor-Informationen.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type8
 */
final readonly class DirectDebitTransaction {
    public function __construct(
        private PaymentIdentification $paymentId,
        private float $amount,
        private CurrencyCode $currency,
        private MandateInformation $mandateInfo,
        private PartyIdentification $debtor,
        private AccountIdentification $debtorAccount,
        private ?FinancialInstitution $debtorAgent = null,
        private ?PartyIdentification $ultimateDebtor = null,
        private ?RemittanceInformation $remittanceInformation = null,
        private ?string $purpose = null
    ) {
    }

    /**
     * Factory für SEPA-Lastschrift.
     */
    public static function sepa(
        string $endToEndId,
        float $amount,
        string $mandateId,
        DateTimeImmutable $mandateDate,
        string $debtorName,
        string $debtorIban,
        ?string $debtorBic = null,
        ?string $remittanceInfo = null
    ): self {
        return new self(
            paymentId: PaymentIdentification::create($endToEndId),
            amount: $amount,
            currency: CurrencyCode::Euro,
            mandateInfo: MandateInformation::create($mandateId, $mandateDate),
            debtor: new PartyIdentification(name: $debtorName),
            debtorAccount: new AccountIdentification(iban: $debtorIban),
            debtorAgent: $debtorBic ? new FinancialInstitution(bic: $debtorBic) : null,
            remittanceInformation: $remittanceInfo ? RemittanceInformation::fromText($remittanceInfo) : null
        );
    }

    public function getPaymentId(): PaymentIdentification {
        return $this->paymentId;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    public function getCurrency(): CurrencyCode {
        return $this->currency;
    }

    public function getMandateInfo(): MandateInformation {
        return $this->mandateInfo;
    }

    public function getDebtor(): PartyIdentification {
        return $this->debtor;
    }

    public function getDebtorAccount(): AccountIdentification {
        return $this->debtorAccount;
    }

    public function getDebtorAgent(): ?FinancialInstitution {
        return $this->debtorAgent;
    }

    public function getUltimateDebtor(): ?PartyIdentification {
        return $this->ultimateDebtor;
    }

    public function getRemittanceInformation(): ?RemittanceInformation {
        return $this->remittanceInformation;
    }

    public function getPurpose(): ?string {
        return $this->purpose;
    }
}
