<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain008DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PaymentIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\RemittanceInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\DirectDebitTransaction;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\GroupHeader;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\MandateInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type8\PaymentInstruction;
use CommonToolkit\FinancialFormats\Enums\Mt\ChargesCode;
use CommonToolkit\FinancialFormats\Enums\Pain\LocalInstrument;
use CommonToolkit\FinancialFormats\Enums\Pain\PaymentMethod;
use CommonToolkit\FinancialFormats\Enums\Pain\SequenceType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use RuntimeException;

/**
 * Builder for pain.008 Documents (Customer Direct Debit Initiation).
 * 
 * Creates direct debit submissions according to ISO 20022.
 * Supports SEPA Core and SEPA B2B direct debits.
 * 
 * Struktur:
 * - GroupHeader: Nachrichten-Metadaten
 * - PaymentInstruction[]: Zahlungsanweisungen mit Lastschriften
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Pain
 */
final class Pain008DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private PartyIdentification $initiatingParty;

    /** @var PaymentInstruction[] */
    private array $paymentInstructions = [];

    /** 
     * Aktive PaymentInstruction die gerade gebaut wird
     * @var DirectDebitInstructionBuilder|null 
     */
    private ?DirectDebitInstructionBuilder $currentInstructionBuilder = null;

    public function __construct() {
        $this->creationDateTime = new DateTimeImmutable();
    }

    /**
     * Setzt die Nachrichten-ID (MsgId).
     * Max. 35 Zeichen, muss eindeutig sein.
     */
    public function setMessageId(string $messageId): self {
        $clone = clone $this;
        $clone->messageId = $messageId;
        return $clone;
    }

    /**
     * Setzt den Erstellungszeitpunkt (CreDtTm).
     */
    public function setCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    /**
     * Setzt die initiierende Partei (InitgPty).
     * Der Auftraggeber der gesamten Nachricht.
     */
    public function setInitiatingParty(PartyIdentification $party): self {
        $clone = clone $this;
        $clone->initiatingParty = $party;
        return $clone;
    }

    /**
     * Adds a completed PaymentInstruction.
     */
    public function addPaymentInstruction(PaymentInstruction $instruction): self {
        $clone = clone $this;
        $clone->paymentInstructions[] = $instruction;
        return $clone;
    }

    /**
     * Starts a new PaymentInstruction for SEPA Core direct debit.
     * Nutze addTransaction() und endPaymentInstruction() zum Fertigstellen.
     * 
     * @param string $paymentInstructionId Unique ID for this instruction
     * @param PartyIdentification $creditor Creditor (payment receiver)
     * @param AccountIdentification $creditorAccount Creditor's account
     * @param string $creditorSchemeId Creditor identification (e.g. DE98ZZZ09999999999)
     * @param SequenceType $sequenceType FIRST, RECURRING, ONE_OFF, FINAL
     * @param FinancialInstitution|null $creditorAgent Creditor's bank
     */
    public function beginSepaCorInstruction(
        string $paymentInstructionId,
        PartyIdentification $creditor,
        AccountIdentification $creditorAccount,
        string $creditorSchemeId,
        SequenceType $sequenceType = SequenceType::ONE_OFF,
        ?FinancialInstitution $creditorAgent = null
    ): self {
        $clone = clone $this;
        $clone->currentInstructionBuilder = new DirectDebitInstructionBuilder(
            $paymentInstructionId,
            $creditor,
            $creditorAccount,
            $creditorSchemeId,
            $sequenceType,
            LocalInstrument::SEPA_CORE,
            $creditorAgent
        );
        return $clone;
    }

    /**
     * Starts a new PaymentInstruction for SEPA B2B direct debit.
     */
    public function beginSepaB2BInstruction(
        string $paymentInstructionId,
        PartyIdentification $creditor,
        AccountIdentification $creditorAccount,
        string $creditorSchemeId,
        SequenceType $sequenceType = SequenceType::ONE_OFF,
        ?FinancialInstitution $creditorAgent = null
    ): self {
        $clone = clone $this;
        $clone->currentInstructionBuilder = new DirectDebitInstructionBuilder(
            $paymentInstructionId,
            $creditor,
            $creditorAccount,
            $creditorSchemeId,
            $sequenceType,
            LocalInstrument::SEPA_B2B,
            $creditorAgent
        );
        return $clone;
    }

    /**
     * Adds a transaction to the current PaymentInstruction.
     * 
     * @throws RuntimeException Wenn keine PaymentInstruction begonnen wurde
     */
    public function addTransaction(DirectDebitTransaction $transaction): self {
        if ($this->currentInstructionBuilder === null) {
            throw new RuntimeException(
                "Keine PaymentInstruction aktiv. Rufe beginSepaCorInstruction() oder beginSepaB2BInstruction() zuerst auf."
            );
        }

        $clone = clone $this;
        $clone->currentInstructionBuilder = $this->currentInstructionBuilder->addTransaction($transaction);
        return $clone;
    }

    /**
     * Sets the requested collection date for the current PaymentInstruction.
     */
    public function setRequestedCollectionDate(DateTimeImmutable $date): self {
        if ($this->currentInstructionBuilder === null) {
            throw new RuntimeException("Keine PaymentInstruction aktiv.");
        }

        $clone = clone $this;
        $clone->currentInstructionBuilder = $this->currentInstructionBuilder->setRequestedCollectionDate($date);
        return $clone;
    }

    /**
     * Sets the ChargesCode for the current PaymentInstruction.
     */
    public function setChargesCode(ChargesCode $code): self {
        if ($this->currentInstructionBuilder === null) {
            throw new RuntimeException("Keine PaymentInstruction aktiv.");
        }

        $clone = clone $this;
        $clone->currentInstructionBuilder = $this->currentInstructionBuilder->setChargesCode($code);
        return $clone;
    }

    /**
     * Enables/Disables batch booking for the current PaymentInstruction.
     */
    public function setBatchBooking(bool $batch): self {
        if ($this->currentInstructionBuilder === null) {
            throw new RuntimeException("Keine PaymentInstruction aktiv.");
        }

        $clone = clone $this;
        $clone->currentInstructionBuilder = $this->currentInstructionBuilder->setBatchBooking($batch);
        return $clone;
    }

    /**
     * Ends the current PaymentInstruction and adds it to the document.
     * 
     * @throws RuntimeException Wenn keine PaymentInstruction aktiv ist
     */
    public function endPaymentInstruction(): self {
        if ($this->currentInstructionBuilder === null) {
            throw new RuntimeException("Keine PaymentInstruction aktiv.");
        }

        $clone = clone $this;
        $clone->paymentInstructions[] = $this->currentInstructionBuilder->build();
        $clone->currentInstructionBuilder = null;
        return $clone;
    }

    /**
     * Erstellt das pain.008 Document.
     * 
     * @throws RuntimeException Wenn Pflichtfelder fehlen
     */
    public function build(): Document {
        if (!isset($this->messageId) || empty($this->messageId)) {
            throw new RuntimeException("MessageId muss angegeben werden.");
        }

        if (!isset($this->initiatingParty)) {
            throw new RuntimeException("InitiatingParty muss angegeben werden.");
        }

        // Falls noch eine PaymentInstruction offen ist, schließen
        $instructions = $this->paymentInstructions;
        if ($this->currentInstructionBuilder !== null) {
            $instructions[] = $this->currentInstructionBuilder->build();
        }

        if (empty($instructions)) {
            throw new RuntimeException("Mindestens eine PaymentInstruction ist erforderlich.");
        }

        // Anzahl und Summe berechnen
        $totalTransactions = array_reduce(
            $instructions,
            fn(int $sum, PaymentInstruction $pi) => $sum + $pi->countTransactions(),
            0
        );

        $controlSum = array_reduce(
            $instructions,
            fn(float $sum, PaymentInstruction $pi) => $sum + $pi->calculateControlSum(),
            0.0
        );

        $groupHeader = new GroupHeader(
            messageId: $this->messageId,
            creationDateTime: $this->creationDateTime,
            numberOfTransactions: $totalTransactions,
            controlSum: $controlSum,
            initiatingParty: $this->initiatingParty
        );

        return new Document($groupHeader, $instructions);
    }

    /**
     * Erstellt eine einfache SEPA Core Lastschrift.
     * 
     * Convenience method for simple direct debits with one transaction.
     */
    public static function createSepaDirectDebit(
        string $messageId,
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        string $creditorSchemeId,
        string $debtorName,
        string $debtorIban,
        float $amount,
        string $mandateId,
        DateTimeImmutable $mandateDate,
        string $reference,
        SequenceType $sequenceType = SequenceType::ONE_OFF
    ): Document {
        $initiatingParty = new PartyIdentification(name: $creditorName);

        $transaction = DirectDebitTransaction::sepa(
            endToEndId: $messageId . '-TXN1',
            amount: $amount,
            mandateId: $mandateId,
            mandateDate: $mandateDate,
            debtorName: $debtorName,
            debtorIban: $debtorIban,
            remittanceInfo: $reference
        );

        $paymentInstruction = PaymentInstruction::sepaCore(
            paymentInstructionId: $messageId . '-PMTINF1',
            collectionDate: new DateTimeImmutable('+5 days'),
            creditorName: $creditorName,
            creditorIban: $creditorIban,
            creditorBic: $creditorBic,
            creditorSchemeId: $creditorSchemeId,
            sequenceType: $sequenceType,
            transactions: [$transaction]
        );

        return Document::create($messageId, $initiatingParty, [$paymentInstruction]);
    }
}

/**
 * Helper builder for DirectDebit PaymentInstruction.
 * Intern verwendet von Pain008DocumentBuilder.
 */
final class DirectDebitInstructionBuilder {
    private DateTimeImmutable $requestedCollectionDate;
    private ChargesCode $chargesCode = ChargesCode::SLEV;
    private ?bool $batchBooking = null;

    /** @var DirectDebitTransaction[] */
    private array $transactions = [];

    public function __construct(
        private readonly string $paymentInstructionId,
        private readonly PartyIdentification $creditor,
        private readonly AccountIdentification $creditorAccount,
        private readonly string $creditorSchemeId,
        private readonly SequenceType $sequenceType,
        private readonly LocalInstrument $localInstrument,
        private readonly ?FinancialInstitution $creditorAgent = null
    ) {
        // Standard: 5 Werktage in der Zukunft für SEPA Core
        $this->requestedCollectionDate = new DateTimeImmutable('+5 days');
    }

    public function setRequestedCollectionDate(DateTimeImmutable $date): self {
        $clone = clone $this;
        $clone->requestedCollectionDate = $date;
        return $clone;
    }

    public function setChargesCode(ChargesCode $code): self {
        $clone = clone $this;
        $clone->chargesCode = $code;
        return $clone;
    }

    public function setBatchBooking(bool $batch): self {
        $clone = clone $this;
        $clone->batchBooking = $batch;
        return $clone;
    }

    public function addTransaction(DirectDebitTransaction $transaction): self {
        $clone = clone $this;
        $clone->transactions[] = $transaction;
        return $clone;
    }

    public function build(): PaymentInstruction {
        if (empty($this->transactions)) {
            throw new RuntimeException("PaymentInstruction muss mindestens eine Transaktion enthalten.");
        }

        return new PaymentInstruction(
            paymentInstructionId: $this->paymentInstructionId,
            paymentMethod: PaymentMethod::DIRECT_DEBIT,
            requestedCollectionDate: $this->requestedCollectionDate,
            creditor: $this->creditor,
            creditorAccount: $this->creditorAccount,
            creditorAgent: $this->creditorAgent ?? new FinancialInstitution(),
            transactions: $this->transactions,
            creditorSchemeId: $this->creditorSchemeId,
            batchBooking: $this->batchBooking,
            chargeBearer: $this->chargesCode,
            sequenceType: $this->sequenceType,
            localInstrument: $this->localInstrument,
            serviceLevel: 'SEPA'
        );
    }
}