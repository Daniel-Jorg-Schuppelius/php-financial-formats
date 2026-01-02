<?php

/*
 * Created on   : Tue Dec 31 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : NotificationItem.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type57;

use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * Notification Item für CAMT.057.
 *
 * Repräsentiert ein einzelnes Element einer Benachrichtigung
 * über einen erwarteten Zahlungseingang.
 *
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type57
 */
class NotificationItem {
    protected string $id;
    protected ?DateTimeImmutable $expectedValueDate = null;
    protected ?float $amount = null;
    protected ?CurrencyCode $currency = null;
    protected ?string $debtorName = null;
    protected ?string $debtorAccountIban = null;
    protected ?string $debtorAgentBic = null;
    protected ?string $remittanceInformation = null;

    public function __construct(
        string $id,
        DateTimeImmutable|string|null $expectedValueDate = null,
        float|string|null $amount = null,
        CurrencyCode|string|null $currency = null,
        ?string $debtorName = null,
        ?string $debtorAccountIban = null,
        ?string $debtorAgentBic = null,
        ?string $remittanceInformation = null
    ) {
        $this->id = $id;
        $this->expectedValueDate = $expectedValueDate instanceof DateTimeImmutable
            ? $expectedValueDate
            : ($expectedValueDate !== null ? new DateTimeImmutable($expectedValueDate) : null);
        $this->amount = is_string($amount) ? (float) $amount : $amount;
        $this->currency = $currency instanceof CurrencyCode
            ? $currency
            : ($currency !== null ? CurrencyCode::from($currency) : null);
        $this->debtorName = $debtorName;
        $this->debtorAccountIban = $debtorAccountIban;
        $this->debtorAgentBic = $debtorAgentBic;
        $this->remittanceInformation = $remittanceInformation;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getExpectedValueDate(): ?DateTimeImmutable {
        return $this->expectedValueDate;
    }

    public function getAmount(): ?float {
        return $this->amount;
    }

    public function getCurrency(): ?CurrencyCode {
        return $this->currency;
    }

    public function getDebtorName(): ?string {
        return $this->debtorName;
    }

    public function getDebtorAccountIban(): ?string {
        return $this->debtorAccountIban;
    }

    public function getDebtorAgentBic(): ?string {
        return $this->debtorAgentBic;
    }

    public function getRemittanceInformation(): ?string {
        return $this->remittanceInformation;
    }
}
