<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Transaction.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Mt1\Type104;

use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Entities\Mt1\TransferDetails;
use CommonToolkit\FinancialFormats\Enums\Mt\ChargesCode;

/**
 * MT104 Single Transaction within a Direct Debit Message.
 * 
 * MT104 contains multiple direct debit transactions (Sequence B) in one message.
 * Each transaction debits an account (debtor) in favor of the creditor.
 * 
 * Fields per transaction:
 * - :21:  Transaction Reference
 * - :21C: End-to-End Reference (optional)
 * - :23E: Instruction Code (optional)
 * - :32B: Currency/Amount
 * - :50a: Creditor (if different from header)
 * - :52a: Creditor's Bank (optional)
 * - :57a: Debtor's Bank
 * - :59a: Debtor (Account to be debited)
 * - :70:  Remittance Information
 * - :71A: Details of Charges
 * - :26T: Transaction Type Code (optional)
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt1\Type104
 */
final readonly class Transaction {
    public function __construct(
        private string $transactionReference,
        private TransferDetails $transferDetails,
        private Party $debtor,
        private ?string $endToEndReference = null,
        private ?string $instructionCode = null,
        private ?Party $creditor = null,
        private ?Party $creditorsBank = null,
        private ?Party $debtorsBank = null,
        private ?string $remittanceInfo = null,
        private ?ChargesCode $chargesCode = null,
        private ?string $transactionTypeCode = null
    ) {
    }

    /**
     * Returns the Transaction Reference (Field :21:).
     */
    public function getTransactionReference(): string {
        return $this->transactionReference;
    }

    /**
     * Returns the End-to-End Reference (Field :21C:).
     */
    public function getEndToEndReference(): ?string {
        return $this->endToEndReference;
    }

    /**
     * Returns the Instruction Code (Field :23E:).
     */
    public function getInstructionCode(): ?string {
        return $this->instructionCode;
    }

    /**
     * Returns the transfer details.
     */
    public function getTransferDetails(): TransferDetails {
        return $this->transferDetails;
    }

    /**
     * Returns the debtor (field :59:) - account to be debited.
     */
    public function getDebtor(): Party {
        return $this->debtor;
    }

    /**
     * Returns the Creditor (Field :50a:) if different from header.
     */
    public function getCreditor(): ?Party {
        return $this->creditor;
    }

    /**
     * Returns the Creditor's Bank (Field :52a:).
     */
    public function getCreditorsBank(): ?Party {
        return $this->creditorsBank;
    }

    /**
     * Returns the Debtor's Bank (Field :57a:).
     */
    public function getDebtorsBank(): ?Party {
        return $this->debtorsBank;
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
     * Returns the Transaction Type Code (Field :26T:).
     */
    public function getTransactionTypeCode(): ?string {
        return $this->transactionTypeCode;
    }

    /**
     * Returns the amount.
     */
    public function getAmount(): float {
        return $this->transferDetails->getAmount();
    }

    /**
     * Serializes as SWIFT fields.
     */
    public function __toString(): string {
        $lines = [];

        $lines[] = ':21:' . $this->transactionReference;

        if ($this->endToEndReference !== null) {
            $lines[] = ':21C:' . $this->endToEndReference;
        }

        if ($this->instructionCode !== null) {
            $lines[] = ':23E:' . $this->instructionCode;
        }

        $lines[] = ':32B:' . $this->transferDetails->toField32B();

        if ($this->creditor !== null) {
            if ($this->creditor->hasAccount()) {
                $lines[] = ':50H:' . $this->creditor->toOptionH();
            } else {
                $lines[] = ':50K:' . $this->creditor->toOptionK();
            }
        }

        if ($this->creditorsBank !== null) {
            if ($this->creditorsBank->isBicOnly()) {
                $lines[] = ':52A:' . $this->creditorsBank->toOptionA();
            } else {
                $lines[] = ':52D:' . $this->creditorsBank->toOptionK();
            }
        }

        if ($this->debtorsBank !== null) {
            if ($this->debtorsBank->isBicOnly()) {
                $lines[] = ':57A:' . $this->debtorsBank->toOptionA();
            } else {
                $lines[] = ':57D:' . $this->debtorsBank->toOptionK();
            }
        }

        $lines[] = ':59:' . $this->debtor->toOptionK();

        if ($this->remittanceInfo !== null) {
            $lines[] = ':70:' . $this->remittanceInfo;
        }

        if ($this->chargesCode !== null) {
            $lines[] = ':71A:' . $this->chargesCode->value;
        }

        if ($this->transactionTypeCode !== null) {
            $lines[] = ':26T:' . $this->transactionTypeCode;
        }

        return implode("\r\n", $lines);
    }
}
