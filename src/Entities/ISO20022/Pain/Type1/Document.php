<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type1;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain\PainDocumentAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Enums\Pain\PainType;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain001Generator;
use DateTimeImmutable;

/**
 * pain.001 Document - Customer Credit Transfer Initiation.
 * 
 * Credit transfer order according to ISO 20022.
 * Equivalent to SWIFT MT101/MT103.
 * 
 * Struktur:
 * - CstmrCdtTrfInitn (Customer Credit Transfer Initiation)
 *   - GrpHdr: Nachrichten-Header
 *   - PmtInf[]: Payment Instructions mit Transaktionen
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type1
 */
final class Document extends PainDocumentAbstract {
    /** @var PaymentInstruction[] */
    private array $paymentInstructions = [];

    public function __construct(
        private GroupHeader $groupHeader,
        array $paymentInstructions = []
    ) {
        $this->paymentInstructions = $paymentInstructions;
    }

    /**
     * Returns the message type.
     */
    public function getType(): PainType {
        return PainType::PAIN_001;
    }

    /**
     * Returns the group header.
     */
    public function getGroupHeader(): GroupHeader {
        return $this->groupHeader;
    }

    /**
     * Returns all payment instructions.
     * @return PaymentInstruction[]
     */
    public function getPaymentInstructions(): array {
        return $this->paymentInstructions;
    }

    /**
     * Adds a payment instruction.
     */
    public function addPaymentInstruction(PaymentInstruction $instruction): void {
        $this->paymentInstructions[] = $instruction;
        $this->updateGroupHeader();
    }

    /**
     * Returns the total number of transactions.
     */
    public function countTransactions(): int {
        return array_reduce(
            $this->paymentInstructions,
            fn(int $sum, PaymentInstruction $pi) => $sum + $pi->countTransactions(),
            0
        );
    }

    /**
     * Berechnet die Kontrollsumme aller Transaktionen.
     */
    public function calculateControlSum(): float {
        return array_reduce(
            $this->paymentInstructions,
            fn(float $sum, PaymentInstruction $pi) => $sum + $pi->calculateControlSum(),
            0.0
        );
    }

    /**
     * Returns all transactions as a flat list.
     * @return CreditTransferTransaction[]
     */
    public function getAllTransactions(): array {
        $transactions = [];
        foreach ($this->paymentInstructions as $instruction) {
            $transactions = array_merge($transactions, $instruction->getTransactions());
        }
        return $transactions;
    }

    /**
     * Aktualisiert den GroupHeader mit berechneten Werten.
     */
    private function updateGroupHeader(): void {
        $this->groupHeader = $this->groupHeader
            ->withTransactionCount($this->countTransactions())
            ->withControlSum($this->calculateControlSum());
    }

    /**
     * Erstellt ein neues pain.001 Dokument.
     */
    public static function create(
        string $messageId,
        PartyIdentification $initiatingParty,
        array $paymentInstructions = []
    ): self {
        $totalTransactions = array_reduce(
            $paymentInstructions,
            fn(int $sum, PaymentInstruction $pi) => $sum + $pi->countTransactions(),
            0
        );

        $controlSum = array_reduce(
            $paymentInstructions,
            fn(float $sum, PaymentInstruction $pi) => $sum + $pi->calculateControlSum(),
            0.0
        );

        $groupHeader = new GroupHeader(
            messageId: $messageId,
            creationDateTime: new DateTimeImmutable(),
            numberOfTransactions: $totalTransactions,
            initiatingParty: $initiatingParty,
            controlSum: $controlSum
        );

        return new self($groupHeader, $paymentInstructions);
    }

    /**
     * Validiert das Dokument.
     * 
     * @return array{valid: bool, errors: string[]}
     */
    public function validate(): array {
        $errors = [];

        // GroupHeader Validierung
        if (strlen($this->groupHeader->getMessageId()) > 35) {
            $errors[] = 'MsgId must not exceed 35 characters';
        }

        if (!$this->groupHeader->getInitiatingParty()->isValid()) {
            $errors[] = 'InitgPty muss mindestens Name oder ID enthalten';
        }

        // Payment Instructions Validierung
        if (empty($this->paymentInstructions)) {
            $errors[] = 'Mindestens eine Payment Instruction erforderlich';
        }

        foreach ($this->paymentInstructions as $index => $instruction) {
            if (strlen($instruction->getPaymentInstructionId()) > 35) {
                $errors[] = "PmtInf[$index]/PmtInfId must not exceed 35 characters";
            }

            if (empty($instruction->getTransactions())) {
                $errors[] = "PmtInf[$index] muss mindestens eine Transaktion enthalten";
            }

            foreach ($instruction->getTransactions() as $txnIndex => $txn) {
                if ($txn->getAmount() <= 0) {
                    $errors[] = "PmtInf[$index]/CdtTrfTxInf[$txnIndex]: Betrag muss größer als 0 sein";
                }

                if (!$txn->getCreditor()->isValid()) {
                    $errors[] = "PmtInf[$index]/CdtTrfTxInf[$txnIndex]: Cdtr muss Name oder ID enthalten";
                }
            }
        }

        // Kontrollsumme prüfen
        $calculatedSum = $this->calculateControlSum();
        if (
            $this->groupHeader->getControlSum() !== null
            && abs($this->groupHeader->getControlSum() - $calculatedSum) > 0.01
        ) {
            $errors[] = sprintf(
                'CtrlSum (%s) stimmt nicht mit berechneter Summe (%s) überein',
                number_format($this->groupHeader->getControlSum(), 2),
                number_format($calculatedSum, 2)
            );
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Generates XML output for this document.
     *
     * @param string|null $namespace Optionaler XML-Namespace (Default: pain.001.001.12)
     * @return string Das generierte XML
     */
    public function toXml(?string $namespace = null): string {
        $generator = $namespace !== null
            ? new Pain001Generator($namespace)
            : new Pain001Generator();
        return $generator->generate($this);
    }
}
