<?php
/*
 * Created on   : Tue Dec 31 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : ModificationRequest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Camt\Type87;

use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * CAMT.087 Modification Request.
 * 
 * Enthält die gewünschten Änderungen an einer Zahlung.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type87
 */
class ModificationRequest {
    private ?string $requestedExecutionDate = null;
    private ?string $requestedSettlementAmount = null;
    private ?CurrencyCode $requestedCurrency = null;
    private ?string $debtorName = null;
    private ?string $debtorAccount = null;
    private ?string $creditorName = null;
    private ?string $creditorAccount = null;
    private ?string $remittanceInformation = null;
    private ?string $purpose = null;

    public function __construct(
        ?string $requestedExecutionDate = null,
        ?string $requestedSettlementAmount = null,
        CurrencyCode|string|null $requestedCurrency = null,
        ?string $debtorName = null,
        ?string $debtorAccount = null,
        ?string $creditorName = null,
        ?string $creditorAccount = null,
        ?string $remittanceInformation = null,
        ?string $purpose = null
    ) {
        $this->requestedExecutionDate = $requestedExecutionDate;
        $this->requestedSettlementAmount = $requestedSettlementAmount;
        $this->requestedCurrency = $requestedCurrency instanceof CurrencyCode
            ? $requestedCurrency
            : ($requestedCurrency !== null ? CurrencyCode::from($requestedCurrency) : null);
        $this->debtorName = $debtorName;
        $this->debtorAccount = $debtorAccount;
        $this->creditorName = $creditorName;
        $this->creditorAccount = $creditorAccount;
        $this->remittanceInformation = $remittanceInformation;
        $this->purpose = $purpose;
    }

    public function getRequestedExecutionDate(): ?string {
        return $this->requestedExecutionDate;
    }

    public function getRequestedSettlementAmount(): ?string {
        return $this->requestedSettlementAmount;
    }

    public function getRequestedCurrency(): ?CurrencyCode {
        return $this->requestedCurrency;
    }

    public function getDebtorName(): ?string {
        return $this->debtorName;
    }

    public function getDebtorAccount(): ?string {
        return $this->debtorAccount;
    }

    public function getCreditorName(): ?string {
        return $this->creditorName;
    }

    public function getCreditorAccount(): ?string {
        return $this->creditorAccount;
    }

    public function getRemittanceInformation(): ?string {
        return $this->remittanceInformation;
    }

    public function getPurpose(): ?string {
        return $this->purpose;
    }

    /**
     * Prüft ob Betrag geändert werden soll.
     */
    public function hasAmountModification(): bool {
        return $this->requestedSettlementAmount !== null;
    }

    /**
     * Prüft ob Debtor geändert werden soll.
     */
    public function hasDebtorModification(): bool {
        return $this->debtorName !== null || $this->debtorAccount !== null;
    }

    /**
     * Prüft ob Creditor geändert werden soll.
     */
    public function hasCreditorModification(): bool {
        return $this->creditorName !== null || $this->creditorAccount !== null;
    }
}
