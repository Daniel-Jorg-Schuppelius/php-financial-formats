<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Transaction.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Mt1\Type101;

use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Entities\Mt1\TransferDetails;
use CommonToolkit\FinancialFormats\Enums\Mt\ChargesCode;
use DateTimeImmutable;

/**
 * MT101 Einzeltransaktion innerhalb eines Request for Transfer.
 * 
 * MT101 kann mehrere Transaktionen (Sequence B) in einer Nachricht enthalten.
 * Jede Transaktion hat eigene Details aber teilt sich gemeinsame Header-Daten.
 * 
 * Felder pro Transaktion:
 * - :21:  Transaction Reference
 * - :32B: Currency/Amount
 * - :57a: Account With Institution
 * - :59a: Beneficiary
 * - :70:  Remittance Information
 * - :71A: Details of Charges
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt1\Type101
 */
final readonly class Transaction {
    public function __construct(
        private string $transactionReference,
        private TransferDetails $transferDetails,
        private Party $beneficiary,
        private ?Party $accountWithInstitution = null,
        private ?string $remittanceInfo = null,
        private ?ChargesCode $chargesCode = null
    ) {
    }

    /**
     * Returns the Transaction Reference (Field :21:).
     */
    public function getTransactionReference(): string {
        return $this->transactionReference;
    }

    /**
     * Returns the transfer details.
     */
    public function getTransferDetails(): TransferDetails {
        return $this->transferDetails;
    }

    /**
     * Returns the beneficiary (field :59:).
     */
    public function getBeneficiary(): Party {
        return $this->beneficiary;
    }

    /**
     * Returns the Account With Institution (Field :57a:).
     */
    public function getAccountWithInstitution(): ?Party {
        return $this->accountWithInstitution;
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
     * Returns the amount.
     */
    public function getAmount(): float {
        return $this->transferDetails->getAmount();
    }

    /**
     * Serialisiert als SWIFT-Felder.
     */
    public function __toString(): string {
        $lines = [];

        $lines[] = ':21:' . $this->transactionReference;
        $lines[] = ':32B:' . $this->transferDetails->toField32B();

        if ($this->accountWithInstitution !== null) {
            if ($this->accountWithInstitution->isBicOnly()) {
                $lines[] = ':57A:' . $this->accountWithInstitution->toOptionA();
            } else {
                $lines[] = ':57D:' . $this->accountWithInstitution->toOptionK();
            }
        }

        $lines[] = ':59:' . $this->beneficiary->toOptionK();

        if ($this->remittanceInfo !== null) {
            $lines[] = ':70:' . $this->remittanceInfo;
        }

        if ($this->chargesCode !== null) {
            $lines[] = ':71A:' . $this->chargesCode->value;
        }

        return implode("\r\n", $lines);
    }
}
