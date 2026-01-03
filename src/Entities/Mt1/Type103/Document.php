<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Mt1\Type103;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt1\MtDocumentAbstract;
use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use CommonToolkit\FinancialFormats\Entities\Mt1\TransferDetails;
use CommonToolkit\FinancialFormats\Enums\BankOperationCode;
use CommonToolkit\FinancialFormats\Enums\ChargesCode;
use CommonToolkit\FinancialFormats\Enums\MtType;
use CommonToolkit\FinancialFormats\Generators\Mt\Mt103Generator;
use DateTimeImmutable;

/**
 * MT103 Document - Single Customer Credit Transfer.
 * 
 * Single transfer according to SWIFT standard. The most common message type
 * for customer payments in international payment transactions.
 * 
 * Pflichtfelder:
 * - :20:  Sender's Reference
 * - :23B: Bank Operation Code (meist CRED)
 * - :32A: Value Date, Currency, Amount
 * - :50a: Ordering Customer
 * - :59a: Beneficiary
 * - :71A: Details of Charges
 * 
 * Optionale Felder:
 * - :33B: Currency/Instructed Amount (for currency conversion)
 * - :36:  Exchange Rate
 * - :52a: Ordering Institution
 * - :53a: Sender's Correspondent
 * - :56a: Intermediary Institution
 * - :57a: Account With Institution
 * - :70:  Remittance Information
 * - :72:  Sender to Receiver Information
 * - :77B: Regulatory Reporting
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt1\Type103
 */
class Document extends MtDocumentAbstract {
    private BankOperationCode $bankOperationCode;
    private ?Party $orderingInstitution;
    private ?Party $sendersCorrespondent;
    private ?Party $intermediaryInstitution;
    private ?Party $accountWithInstitution;
    private ?string $senderToReceiverInfo;
    private ?string $regulatoryReporting;
    private ?string $transactionTypeCode;

    public function __construct(
        string $sendersReference,
        TransferDetails $transferDetails,
        Party $orderingCustomer,
        Party $beneficiary,
        ?BankOperationCode $bankOperationCode = null,
        ?ChargesCode $chargesCode = null,
        ?string $remittanceInfo = null,
        ?Party $orderingInstitution = null,
        ?Party $sendersCorrespondent = null,
        ?Party $intermediaryInstitution = null,
        ?Party $accountWithInstitution = null,
        ?string $senderToReceiverInfo = null,
        ?string $regulatoryReporting = null,
        ?string $transactionTypeCode = null,
        ?DateTimeImmutable $creationDateTime = null
    ) {
        parent::__construct(
            $sendersReference,
            $transferDetails,
            $orderingCustomer,
            $beneficiary,
            $remittanceInfo,
            $chargesCode,
            $creationDateTime
        );

        $this->bankOperationCode = $bankOperationCode ?? BankOperationCode::CRED;
        $this->orderingInstitution = $orderingInstitution;
        $this->sendersCorrespondent = $sendersCorrespondent;
        $this->intermediaryInstitution = $intermediaryInstitution;
        $this->accountWithInstitution = $accountWithInstitution;
        $this->senderToReceiverInfo = $senderToReceiverInfo;
        $this->regulatoryReporting = $regulatoryReporting;
        $this->transactionTypeCode = $transactionTypeCode;
    }

    public function getMtType(): MtType {
        return MtType::MT103;
    }

    /**
     * Returns the Bank Operation Code (Field :23B:).
     */
    public function getBankOperationCode(): BankOperationCode {
        return $this->bankOperationCode;
    }

    /**
     * Returns the Ordering Institution (Field :52a:).
     * Die Bank des Auftraggebers.
     */
    public function getOrderingInstitution(): ?Party {
        return $this->orderingInstitution;
    }

    /**
     * Returns the Sender's Correspondent (Field :53a:).
     * Die Korrespondenzbank des Senders.
     */
    public function getSendersCorrespondent(): ?Party {
        return $this->sendersCorrespondent;
    }

    /**
     * Returns the Intermediary Institution (Field :56a:).
     * Zwischenbank im Zahlungsweg.
     */
    public function getIntermediaryInstitution(): ?Party {
        return $this->intermediaryInstitution;
    }

    /**
     * Returns the Account With Institution (Field :57a:).
     * The beneficiary's bank.
     */
    public function getAccountWithInstitution(): ?Party {
        return $this->accountWithInstitution;
    }

    /**
     * Returns the Sender to Receiver Information (Field :72:).
     */
    public function getSenderToReceiverInfo(): ?string {
        return $this->senderToReceiverInfo;
    }

    /**
     * Returns the Regulatory Reporting Information (Field :77B:).
     */
    public function getRegulatoryReporting(): ?string {
        return $this->regulatoryReporting;
    }

    /**
     * Returns the Transaction Type Code (Field :26T:).
     */
    public function getTransactionTypeCode(): ?string {
        return $this->transactionTypeCode;
    }

    /**
     * Checks if this is an STP-capable message.
     * (Straight Through Processing)
     */
    public function isStpCapable(): bool {
        // STP erfordert minimale Felder und bestimmte Formate
        return $this->orderingCustomer->hasAccount()
            && $this->beneficiary->hasAccount();
    }

    /**
     * Creates a copy with changed charges.
     */
    public function withChargesCode(ChargesCode $chargesCode): self {
        return new self(
            $this->sendersReference,
            $this->transferDetails,
            $this->orderingCustomer,
            $this->beneficiary,
            $this->bankOperationCode,
            $chargesCode,
            $this->remittanceInfo,
            $this->orderingInstitution,
            $this->sendersCorrespondent,
            $this->intermediaryInstitution,
            $this->accountWithInstitution,
            $this->senderToReceiverInfo,
            $this->regulatoryReporting,
            $this->transactionTypeCode,
            $this->creationDateTime
        );
    }

    /**
     * Generiert die SWIFT MT103 Nachricht.
     */
    public function __toString(): string {
        return (new Mt103Generator())->generate($this);
    }
}
