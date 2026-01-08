<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CreditTransferTransaction.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type1;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PaymentIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\RemittanceInformation;
use CommonToolkit\FinancialFormats\Enums\Mt\ChargesCode;
use CommonToolkit\Enums\CurrencyCode;

/**
 * Credit transfer transaction for pain.001 messages (CdtTrfTxInf).
 * 
 * Single transfer within a PaymentInstruction.
 * Contains all details about amount, recipient and remittance information.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type1
 */
final readonly class CreditTransferTransaction {
    public function __construct(
        private PaymentIdentification $paymentId,
        private float $amount,
        private CurrencyCode $currency,
        private PartyIdentification $creditor,
        private ?AccountIdentification $creditorAccount = null,
        private ?FinancialInstitution $creditorAgent = null,
        private ?PartyIdentification $ultimateCreditor = null,
        private ?RemittanceInformation $remittanceInformation = null,
        private ?ChargesCode $chargeBearer = null,
        private ?string $purposeCode = null,
        private ?float $exchangeRate = null,
        private ?CurrencyCode $instructedCurrency = null,
        private ?float $instructedAmount = null
    ) {
    }

    /**
     * Returns the payment identification (PmtId).
     */
    public function getPaymentId(): PaymentIdentification {
        return $this->paymentId;
    }

    /**
     * Returns the amount (Amt/InstdAmt).
     */
    public function getAmount(): float {
        return $this->amount;
    }

    /**
     * Returns the currency.
     */
    public function getCurrency(): CurrencyCode {
        return $this->currency;
    }

    /**
     * Returns the beneficiary (Cdtr).
     */
    public function getCreditor(): PartyIdentification {
        return $this->creditor;
    }

    /**
     * Returns the beneficiary account (CdtrAcct).
     */
    public function getCreditorAccount(): ?AccountIdentification {
        return $this->creditorAccount;
    }

    /**
     * Returns the beneficiary bank (CdtrAgt).
     */
    public function getCreditorAgent(): ?FinancialInstitution {
        return $this->creditorAgent;
    }

    /**
     * Returns the ultimate beneficiary (UltmtCdtr).
     */
    public function getUltimateCreditor(): ?PartyIdentification {
        return $this->ultimateCreditor;
    }

    /**
     * Returns the remittance information (RmtInf).
     */
    public function getRemittanceInformation(): ?RemittanceInformation {
        return $this->remittanceInformation;
    }

    /**
     * Returns the charge bearer (ChrgBr).
     */
    public function getChargeBearer(): ?ChargesCode {
        return $this->chargeBearer;
    }

    /**
     * Returns the purpose code (Purp/Cd).
     * E.g. SALA for salary, RENT for rent.
     */
    public function getPurposeCode(): ?string {
        return $this->purposeCode;
    }

    /**
     * Returns the exchange rate (XchgRateInf).
     */
    public function getExchangeRate(): ?float {
        return $this->exchangeRate;
    }

    /**
     * Returns the originally instructed currency.
     */
    public function getInstructedCurrency(): ?CurrencyCode {
        return $this->instructedCurrency;
    }

    /**
     * Returns the originally instructed amount.
     */
    public function getInstructedAmount(): ?float {
        return $this->instructedAmount;
    }

    /**
     * Creates a simple transfer.
     */
    public static function create(
        string $endToEndId,
        float $amount,
        CurrencyCode $currency,
        PartyIdentification $creditor,
        AccountIdentification $creditorAccount,
        ?string $remittanceInfo = null
    ): self {
        return new self(
            paymentId: PaymentIdentification::fromEndToEndId($endToEndId),
            amount: $amount,
            currency: $currency,
            creditor: $creditor,
            creditorAccount: $creditorAccount,
            remittanceInformation: $remittanceInfo !== null
                ? RemittanceInformation::fromText($remittanceInfo)
                : null
        );
    }

    /**
     * Creates a SEPA transfer with BIC.
     */
    public static function sepa(
        string $endToEndId,
        float $amount,
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        ?string $remittanceInfo = null
    ): self {
        return new self(
            paymentId: PaymentIdentification::fromEndToEndId($endToEndId),
            amount: $amount,
            currency: CurrencyCode::Euro,
            creditor: PartyIdentification::fromName($creditorName),
            creditorAccount: AccountIdentification::fromIban($creditorIban),
            creditorAgent: FinancialInstitution::fromBic($creditorBic),
            remittanceInformation: $remittanceInfo !== null
                ? RemittanceInformation::fromText($remittanceInfo)
                : null,
            chargeBearer: ChargesCode::SLEV // SEPA-Level
        );
    }
}
