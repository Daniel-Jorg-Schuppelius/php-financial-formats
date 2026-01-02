<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt056DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type56\UnderlyingTransaction;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder für CAMT.056 Documents (FI To FI Payment Cancellation Request).
 * 
 * Erstellt Stornierungsanfragen von Bank zu Bank.
 * 
 * Verwendung:
 * ```php
 * $document = Camt056DocumentBuilder::create('MSG-001')
 *     ->withInstructingAgent('COBADEFFXXX')
 *     ->withInstructedAgent('DEUTDEFFXXX')
 *     ->forCase('CASE-001', 'COBADEFFXXX')
 *     ->addUnderlyingTransaction($transaction)
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Camt
 */
final class Camt056DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private ?string $numberOfTransactions = null;
    private ?string $controlSum = null;
    private ?string $instructingAgentBic = null;
    private ?string $instructedAgentBic = null;
    private ?string $caseId = null;
    private ?string $caseCreator = null;

    /** @var UnderlyingTransaction[] */
    private array $underlyingTransactions = [];

    private function __construct(string $messageId) {
        if (strlen($messageId) > 35) {
            throw new InvalidArgumentException('MsgId darf maximal 35 Zeichen lang sein');
        }
        $this->messageId = $messageId;
        $this->creationDateTime = new DateTimeImmutable();
    }

    /**
     * Erzeugt neuen Builder mit Message-ID.
     */
    public static function create(string $messageId): self {
        return new self($messageId);
    }

    /**
     * Setzt den Erstellungszeitpunkt (Standard: jetzt).
     */
    public function withCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    /**
     * Setzt die Anzahl der Transaktionen.
     */
    public function withNumberOfTransactions(int $count): self {
        $clone = clone $this;
        $clone->numberOfTransactions = (string) $count;
        return $clone;
    }

    /**
     * Setzt die Kontrollsumme.
     */
    public function withControlSum(float $sum): self {
        $clone = clone $this;
        $clone->controlSum = number_format($sum, 2, '.', '');
        return $clone;
    }

    /**
     * Setzt die anweisende Bank (Instructing Agent).
     */
    public function withInstructingAgent(string $bic): self {
        $clone = clone $this;
        $clone->instructingAgentBic = $bic;
        return $clone;
    }

    /**
     * Setzt die angewiesene Bank (Instructed Agent).
     */
    public function withInstructedAgent(string $bic): self {
        $clone = clone $this;
        $clone->instructedAgentBic = $bic;
        return $clone;
    }

    /**
     * Setzt die Case-Referenz.
     */
    public function forCase(string $caseId, ?string $caseCreator = null): self {
        $clone = clone $this;
        $clone->caseId = $caseId;
        $clone->caseCreator = $caseCreator;
        return $clone;
    }

    /**
     * Fügt eine Underlying Transaction hinzu.
     */
    public function addUnderlyingTransaction(UnderlyingTransaction $transaction): self {
        $clone = clone $this;
        $clone->underlyingTransactions[] = $transaction;
        return $clone;
    }

    /**
     * Fügt mehrere Underlying Transactions hinzu.
     * 
     * @param UnderlyingTransaction[] $transactions
     */
    public function addUnderlyingTransactions(array $transactions): self {
        $clone = clone $this;
        $clone->underlyingTransactions = array_merge($clone->underlyingTransactions, $transactions);
        return $clone;
    }

    /**
     * Erstellt das CAMT.056 Document.
     * 
     * @throws InvalidArgumentException wenn Pflichtfelder fehlen
     */
    public function build(): Document {
        if (empty($this->underlyingTransactions)) {
            throw new InvalidArgumentException('Mindestens eine Underlying Transaction erforderlich');
        }

        // Automatisch NumberOfTransactions berechnen wenn nicht gesetzt
        $numberOfTransactions = $this->numberOfTransactions
            ?? (string) count($this->underlyingTransactions);

        $document = new Document(
            messageId: $this->messageId,
            creationDateTime: $this->creationDateTime,
            numberOfTransactions: $numberOfTransactions,
            controlSum: $this->controlSum,
            instructingAgentBic: $this->instructingAgentBic,
            instructedAgentBic: $this->instructedAgentBic,
            caseId: $this->caseId,
            caseCreator: $this->caseCreator
        );

        foreach ($this->underlyingTransactions as $transaction) {
            $document->addUnderlyingTransaction($transaction);
        }

        return $document;
    }

    // === Static Factory Methods ===

    /**
     * Erstellt eine einfache Bank-zu-Bank Stornierungsanfrage.
     */
    public static function createSimple(
        string $messageId,
        string $instructingAgentBic,
        string $instructedAgentBic,
        string $originalMessageId,
        string $originalMessageNameId
    ): Document {
        $underlying = new UnderlyingTransaction(
            originalGroupInformationMessageId: $originalMessageId,
            originalGroupInformationMessageNameId: $originalMessageNameId
        );

        return self::create($messageId)
            ->withInstructingAgent($instructingAgentBic)
            ->withInstructedAgent($instructedAgentBic)
            ->addUnderlyingTransaction($underlying)
            ->build();
    }

    /**
     * Erstellt eine Stornierungsanfrage mit Case-Referenz.
     */
    public static function createWithCase(
        string $messageId,
        string $instructingAgentBic,
        string $instructedAgentBic,
        string $caseId,
        string $originalMessageId,
        string $originalMessageNameId
    ): Document {
        $underlying = new UnderlyingTransaction(
            originalGroupInformationMessageId: $originalMessageId,
            originalGroupInformationMessageNameId: $originalMessageNameId
        );

        return self::create($messageId)
            ->withInstructingAgent($instructingAgentBic)
            ->withInstructedAgent($instructedAgentBic)
            ->forCase($caseId, $instructingAgentBic)
            ->addUnderlyingTransaction($underlying)
            ->build();
    }
}
