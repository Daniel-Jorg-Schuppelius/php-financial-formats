<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CamtBalance.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\BalanceSubType;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Represents a balance in CAMT documents.
 * 
 * Supported balance types:
 * - OPBD: Opening Booked
 * - CLBD: Closing Booked (Schlusssaldo)
 * - PRCD: Previously Closed Booked (Vortrag)
 * - CLAV: Closing Available
 * - FWAV: Forward Available
 * 
 * @package CommonToolkit\Entities\Common\Banking
 */
class Balance {
    private CreditDebit $creditDebit;
    private DateTimeImmutable $date;
    private CurrencyCode $currency;
    private float $amount;
    private string $type;
    private ?BalanceSubType $subType;

    /**
     * @param CreditDebit $creditDebit Soll/Haben-Kennzeichen
     * @param DateTimeImmutable|string $date Datum des Saldos
     * @param CurrencyCode|string $currency Currency
     * @param float $amount Betrag (positiv)
     * @param string $type Balance-Typ (OPBD, CLBD, PRCD, CLAV, FWAV)
     * @param BalanceSubType|string|null $subType ISO 20022 Balance Untertyp
     */
    public function __construct(
        CreditDebit $creditDebit,
        DateTimeImmutable|string $date,
        CurrencyCode|string $currency,
        float $amount,
        string $type = 'CLBD',
        BalanceSubType|string|null $subType = null
    ) {
        $this->creditDebit = $creditDebit;

        $this->date = $date instanceof DateTimeImmutable
            ? $date
            : new DateTimeImmutable($date);

        $this->currency = $currency instanceof CurrencyCode
            ? $currency
            : CurrencyCode::tryFrom(strtoupper($currency))
            ?? throw new InvalidArgumentException("Ungültige Währung: $currency");

        $this->amount = abs($amount);
        $this->type = strtoupper($type);
        $this->subType = $subType instanceof BalanceSubType ? $subType : BalanceSubType::tryFrom($subType ?? '');
    }

    public function getCreditDebit(): CreditDebit {
        return $this->creditDebit;
    }

    public function getDate(): DateTimeImmutable {
        return $this->date;
    }

    public function getCurrency(): CurrencyCode {
        return $this->currency;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getSubType(): ?BalanceSubType {
        return $this->subType;
    }

    public function isCredit(): bool {
        return $this->creditDebit === CreditDebit::CREDIT;
    }

    public function isDebit(): bool {
        return $this->creditDebit === CreditDebit::DEBIT;
    }

    /**
     * Returns the signed amount.
     * Positive for credit, negative for debit.
     */
    public function getSignedAmount(): float {
        return $this->isCredit() ? $this->amount : -$this->amount;
    }

    /**
     * Checks if this is an opening balance.
     */
    public function isOpeningBalance(): bool {
        return in_array($this->type, ['OPBD', 'PRCD'], true);
    }

    /**
     * Checks if this is a closing balance.
     */
    public function isClosingBalance(): bool {
        return $this->type === 'CLBD';
    }

    /**
     * Checks if this is a closing available balance.
     */
    public function isClosingAvailable(): bool {
        return $this->type === 'CLAV';
    }

    /**
     * Creates a Balance from XML data.
     */
    public static function fromArray(array $data): self {
        return new self(
            creditDebit: CreditDebit::from($data['creditDebit'] ?? 'CRDT'),
            date: $data['date'] ?? 'now',
            currency: $data['currency'] ?? 'EUR',
            amount: (float) ($data['amount'] ?? 0.0),
            type: $data['type'] ?? 'CLBD'
        );
    }

    /**
     * Returns a string representation of the balance.
     * Format: "OPBD: C 2025-01-15 EUR 1000.00"
     */
    public function __toString(): string {
        return sprintf(
            '%s: %s %s %s %.2f',
            $this->type,
            $this->creditDebit === CreditDebit::CREDIT ? 'C' : 'D',
            $this->date->format('Y-m-d'),
            $this->currency->value,
            $this->amount
        );
    }
}
