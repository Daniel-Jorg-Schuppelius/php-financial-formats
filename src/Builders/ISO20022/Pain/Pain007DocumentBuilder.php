<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain007DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\GroupHeader;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\OriginalGroupInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\OriginalPaymentInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\TransactionInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type7\ReversalReason;
use DateTimeImmutable;
use RuntimeException;

/**
 * Builder für pain.007 Documents (Customer Payment Reversal).
 * 
 * Erstellt Lastschrift-Stornierungen gemäß ISO 20022.
 * Ermöglicht den Rückruf von bereits eingereichten Lastschriften (pain.008).
 * 
 * Struktur:
 * - GroupHeader: Nachrichten-Metadaten
 * - OriginalGroupInformation: Referenz zur Original-Nachricht
 * - OriginalPaymentInformation[]: Stornierungsinformationen
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Pain
 */
final class Pain007DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private PartyIdentification $initiatingParty;
    private ?OriginalGroupInformation $originalGroupInformation = null;

    /** @var OriginalPaymentInformation[] */
    private array $originalPaymentInformations = [];

    /** @var ReversalInstructionBuilder|null */
    private ?ReversalInstructionBuilder $currentInstructionBuilder = null;

    public function __construct() {
        $this->creationDateTime = new DateTimeImmutable();
    }

    /**
     * Setzt die Nachrichten-ID (MsgId).
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
     * Setzt die initiierende Partei.
     */
    public function setInitiatingParty(PartyIdentification $party): self {
        $clone = clone $this;
        $clone->initiatingParty = $party;
        return $clone;
    }

    /**
     * Setzt die Original Group Information.
     */
    public function setOriginalGroupInformation(OriginalGroupInformation $info): self {
        $clone = clone $this;
        $clone->originalGroupInformation = $info;
        return $clone;
    }

    /**
     * Convenience: Setzt Referenz auf eine pain.008 Nachricht.
     */
    public function forPain008(string $originalMessageId): self {
        $clone = clone $this;
        $clone->originalGroupInformation = new OriginalGroupInformation(
            originalMessageId: $originalMessageId,
            originalMessageNameId: 'pain.008.001.11'
        );
        return $clone;
    }

    /**
     * Startet eine neue Reversal-Anweisung für eine Payment Instruction.
     */
    public function beginReversalInstruction(string $originalPaymentInformationId): self {
        $clone = clone $this;
        $clone->currentInstructionBuilder = new ReversalInstructionBuilder($originalPaymentInformationId);
        return $clone;
    }

    /**
     * Fügt eine zu stornierende Transaktion hinzu.
     */
    public function addTransactionReversal(TransactionInformation $transaction): self {
        if ($this->currentInstructionBuilder === null) {
            throw new RuntimeException("Keine Reversal-Anweisung aktiv. Rufe beginReversalInstruction() zuerst auf.");
        }

        $clone = clone $this;
        $clone->currentInstructionBuilder = $this->currentInstructionBuilder->addTransaction($transaction);
        return $clone;
    }

    /**
     * Setzt den Stornogrund für die aktuelle Anweisung.
     */
    public function setReversalReason(ReversalReason $reason): self {
        if ($this->currentInstructionBuilder === null) {
            throw new RuntimeException("Keine Reversal-Anweisung aktiv.");
        }

        $clone = clone $this;
        $clone->currentInstructionBuilder = $this->currentInstructionBuilder->setReversalReason($reason);
        return $clone;
    }

    /**
     * Beendet die aktuelle Reversal-Anweisung.
     */
    public function endReversalInstruction(): self {
        if ($this->currentInstructionBuilder === null) {
            throw new RuntimeException("Keine Reversal-Anweisung aktiv.");
        }

        $clone = clone $this;
        $clone->originalPaymentInformations[] = $this->currentInstructionBuilder->build();
        $clone->currentInstructionBuilder = null;
        return $clone;
    }

    /**
     * Fügt eine fertige Payment Information hinzu.
     */
    public function addOriginalPaymentInformation(OriginalPaymentInformation $info): self {
        $clone = clone $this;
        $clone->originalPaymentInformations[] = $info;
        return $clone;
    }

    /**
     * Erstellt das pain.007 Document.
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

        if ($this->originalGroupInformation === null) {
            throw new RuntimeException("OriginalGroupInformation muss angegeben werden.");
        }

        // Falls noch eine Anweisung offen ist, schließen
        $infos = $this->originalPaymentInformations;
        if ($this->currentInstructionBuilder !== null) {
            $infos[] = $this->currentInstructionBuilder->build();
        }

        return Document::create(
            $this->messageId,
            $this->initiatingParty,
            $this->originalGroupInformation,
            $infos
        );
    }

    /**
     * Erstellt eine einfache Stornierung für eine einzelne Lastschrift.
     */
    public static function createSingleReversal(
        string $messageId,
        string $initiatorName,
        string $originalMessageId,
        string $originalPaymentInformationId,
        string $originalEndToEndId,
        float $amount,
        string $reasonCode = 'CUST',
        ?string $additionalInfo = null
    ): Document {
        $initiatingParty = new PartyIdentification(name: $initiatorName);

        $reason = ReversalReason::fromCode($reasonCode, $additionalInfo !== null ? [$additionalInfo] : []);

        $transaction = TransactionInformation::create(
            originalEndToEndId: $originalEndToEndId,
            reversedAmount: $amount,
            reason: $reason
        );

        $paymentInfo = OriginalPaymentInformation::create(
            originalPaymentInformationId: $originalPaymentInformationId,
            transactionInformations: [$transaction],
            reversalReason: $reason
        );

        $originalGroupInfo = new OriginalGroupInformation(
            originalMessageId: $originalMessageId,
            originalMessageNameId: 'pain.008.001.11'
        );

        return Document::create(
            $messageId,
            $initiatingParty,
            $originalGroupInfo,
            [$paymentInfo]
        );
    }
}

/**
 * Hilfsbuilder für Reversal PaymentInstruction.
 * Intern verwendet von Pain007DocumentBuilder.
 */
final class ReversalInstructionBuilder {
    /** @var TransactionInformation[] */
    private array $transactionInformations = [];
    private ?ReversalReason $reversalReason = null;

    public function __construct(
        private readonly string $originalPaymentInformationId
    ) {
    }

    public function addTransaction(TransactionInformation $transaction): self {
        $clone = clone $this;
        $clone->transactionInformations[] = $transaction;
        return $clone;
    }

    public function setReversalReason(ReversalReason $reason): self {
        $clone = clone $this;
        $clone->reversalReason = $reason;
        return $clone;
    }

    public function build(): OriginalPaymentInformation {
        return OriginalPaymentInformation::create(
            originalPaymentInformationId: $this->originalPaymentInformationId,
            transactionInformations: $this->transactionInformations,
            reversalReason: $this->reversalReason
        );
    }
}
