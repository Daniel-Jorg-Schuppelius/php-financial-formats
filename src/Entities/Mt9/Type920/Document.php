<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Mt9\Type920;

use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use DateTimeImmutable;

/**
 * MT920 Document - Request Message.
 * 
 * Sent by an account owner to request account statement information.
 * Used to request MT940, MT941, MT942, or MT950 messages.
 * 
 * Fields:
 * - :20: Transaction Reference Number (M)
 * - :12: Requested Message Type (M) - 940, 941, 942, or 950
 * - :25: Account Identification (M)
 * - :34F: Floor Limit Indicator (O) - Currency and amount threshold
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt9\Type920
 */
final readonly class Document {
    public function __construct(
        private string $transactionReference,
        private string $requestedMessageType,
        private string $accountId,
        private ?string $floorLimitCurrency = null,
        private ?float $floorLimitAmount = null,
        private ?string $floorLimitIndicator = null,
        private ?DateTimeImmutable $creationDateTime = null
    ) {
    }

    /**
     * Returns the MT message type.
     */
    public function getMtType(): MtType {
        return MtType::MT920;
    }

    /**
     * Returns the transaction reference.
     * Field :20:
     */
    public function getTransactionReference(): string {
        return $this->transactionReference;
    }

    /**
     * Returns the requested message type (940, 941, 942, 950).
     * Field :12:
     */
    public function getRequestedMessageType(): string {
        return $this->requestedMessageType;
    }

    /**
     * Returns the account identification.
     * Field :25:
     */
    public function getAccountId(): string {
        return $this->accountId;
    }

    /**
     * Returns the floor limit currency.
     * Part of Field :34F:
     */
    public function getFloorLimitCurrency(): ?string {
        return $this->floorLimitCurrency;
    }

    /**
     * Returns the floor limit amount.
     * Part of Field :34F:
     */
    public function getFloorLimitAmount(): ?float {
        return $this->floorLimitAmount;
    }

    /**
     * Returns the floor limit indicator (D for Debit, C for Credit).
     * Part of Field :34F:
     */
    public function getFloorLimitIndicator(): ?string {
        return $this->floorLimitIndicator;
    }

    /**
     * Returns the creation date/time.
     */
    public function getCreationDateTime(): ?DateTimeImmutable {
        return $this->creationDateTime;
    }

    /**
     * Checks if a floor limit is set.
     */
    public function hasFloorLimit(): bool {
        return $this->floorLimitCurrency !== null && $this->floorLimitAmount !== null;
    }

    /**
     * Formats the floor limit as Field 34F.
     * Format: [Currency][D/C][Amount]
     */
    public function toField34F(): ?string {
        if (!$this->hasFloorLimit()) {
            return null;
        }

        $indicator = $this->floorLimitIndicator ?? '';
        $amount = number_format($this->floorLimitAmount, 2, ',', '');

        return $this->floorLimitCurrency . $indicator . $amount;
    }
}
