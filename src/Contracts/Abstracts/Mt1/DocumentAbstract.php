<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DocumentAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt1;

use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Entities\Mt1\TransferDetails;
use CommonToolkit\FinancialFormats\Enums\Mt\ChargesCode;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * Abstract base class for MT10x documents (payment orders).
 * 
 * Common fields for MT101, MT103, MT104:
 * - :20:  Sender's Reference
 * - :23B: Bank Operation Code
 * - :32A/B: Value Date, Currency, Amount
 * - :50:  Ordering Customer (Auftraggeber)
 * - :59:  Beneficiary
 * - :70:  Remittance Information (Verwendungszweck)
 * - :71A: Details of Charges
 * 
 * @package CommonToolkit\Contracts\Abstracts\Common\Banking\Mt1
 */
abstract class DocumentAbstract {
    protected string $sendersReference;
    protected TransferDetails $transferDetails;
    protected Party $orderingCustomer;
    protected Party $beneficiary;
    protected ?string $remittanceInfo;
    protected ?ChargesCode $chargesCode;
    protected ?DateTimeImmutable $creationDateTime;

    public function __construct(
        string $sendersReference,
        TransferDetails $transferDetails,
        Party $orderingCustomer,
        Party $beneficiary,
        ?string $remittanceInfo = null,
        ?ChargesCode $chargesCode = null,
        ?DateTimeImmutable $creationDateTime = null
    ) {
        $this->sendersReference = $sendersReference;
        $this->transferDetails = $transferDetails;
        $this->orderingCustomer = $orderingCustomer;
        $this->beneficiary = $beneficiary;
        $this->remittanceInfo = $remittanceInfo;
        $this->chargesCode = $chargesCode;
        $this->creationDateTime = $creationDateTime ?? new DateTimeImmutable();
    }

    /**
     * Returns the MT type.
     */
    abstract public function getMtType(): MtType;

    /**
     * Returns the sender's reference (field :20:).
     */
    public function getSendersReference(): string {
        return $this->sendersReference;
    }

    /**
     * Returns the transfer details.
     */
    public function getTransferDetails(): TransferDetails {
        return $this->transferDetails;
    }

    /**
     * Returns the ordering customer (field :50:).
     */
    public function getOrderingCustomer(): Party {
        return $this->orderingCustomer;
    }

    /**
     * Returns the beneficiary (field :59:).
     */
    public function getBeneficiary(): Party {
        return $this->beneficiary;
    }

    /**
     * Returns the remittance information (field :70:).
     */
    public function getRemittanceInfo(): ?string {
        return $this->remittanceInfo;
    }

    /**
     * Returns the charges code (field :71A:).
     */
    public function getChargesCode(): ?ChargesCode {
        return $this->chargesCode;
    }

    /**
     * Returns the creation date.
     */
    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    /**
     * Returns the value date.
     */
    public function getValueDate(): DateTimeImmutable {
        return $this->transferDetails->getValueDate();
    }

    /**
     * Returns the currency.
     */
    public function getCurrency(): CurrencyCode {
        return $this->transferDetails->getCurrency();
    }

    /**
     * Returns the amount.
     */
    public function getAmount(): float {
        return $this->transferDetails->getAmount();
    }

    /**
     * Returns the formatted amount.
     */
    public function getFormattedAmount(): string {
        return $this->transferDetails->getFormattedAmount();
    }

    /**
     * Generates the common SWIFT fields.
     * 
     * @return array<string, string>
     */
    protected function getCommonSwiftFields(): array {
        $fields = [
            ':20:' => $this->sendersReference,
        ];

        return $fields;
    }

    /**
     * Muss von konkreten Klassen implementiert werden.
     */
    abstract public function __toString(): string;
}
