<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt052DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Converters\Banking\MtInterConverter;
use CommonToolkit\FinancialFormats\Converters\Banking\Mt940ToCamtConverter;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Balance;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52\Transaction;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type940\Document as Mt940Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type941\Document as Mt941Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type942\Document as Mt942Document;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\ReportingSource;
use CommonToolkit\Enums\CreditDebit;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

/**
 * Builder for CAMT.052 Documents (Bank to Customer Account Report).
 * 
 * CAMT.052 contains intraday account movements with optional balances.
 * Equivalent to MT942 in SWIFT format.
 * 
 * @package CommonToolkit\Builders
 */
final class Camt052DocumentBuilder {
    private string $id;
    private DateTimeImmutable $creationDateTime;
    private string $accountIdentifier;
    private CurrencyCode $currency;
    private ?string $accountOwner = null;
    private ?string $servicerBic = null;
    private ?string $messageId = null;
    private ?string $sequenceNumber = null;
    private ?ReportingSource $reportingSource = null;

    private ?Balance $openingBalance = null;
    private ?Balance $closingBalance = null;

    /** @var Transaction[] */
    private array $entries = [];

    public function __construct() {
        $this->creationDateTime = new DateTimeImmutable();
    }

    public function setId(string $id): self {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function setCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    public function setAccountIdentifier(string $identifier): self {
        $clone = clone $this;
        $clone->accountIdentifier = $identifier;
        return $clone;
    }

    public function setCurrency(CurrencyCode $currency): self {
        $clone = clone $this;
        $clone->currency = $currency;
        return $clone;
    }

    public function setAccountOwner(string $owner): self {
        $clone = clone $this;
        $clone->accountOwner = $owner;
        return $clone;
    }

    public function setServicerBic(string $bic): self {
        $clone = clone $this;
        $clone->servicerBic = $bic;
        return $clone;
    }

    public function setMessageId(string $messageId): self {
        $clone = clone $this;
        $clone->messageId = $messageId;
        return $clone;
    }

    public function setSequenceNumber(string $sequenceNumber): self {
        $clone = clone $this;
        $clone->sequenceNumber = $sequenceNumber;
        return $clone;
    }

    public function setOpeningBalance(Balance $balance): self {
        $clone = clone $this;
        $clone->openingBalance = $balance;
        return $clone;
    }

    public function setClosingBalance(Balance $balance): self {
        $clone = clone $this;
        $clone->closingBalance = $balance;
        return $clone;
    }

    public function addEntry(Transaction $entry): self {
        $clone = clone $this;
        $clone->entries[] = $entry;
        return $clone;
    }

    /**
     * Adds multiple transactions.
     * 
     * @param Transaction[] $entries
     * @throws InvalidArgumentException Wenn ein Element kein Transaction-Objekt ist
     */
    public function addEntries(array $entries): self {
        $clone = clone $this;
        foreach ($entries as $entry) {
            if (!$entry instanceof Transaction) {
                throw new InvalidArgumentException('Alle Elemente müssen vom Typ Transaction sein.');
            }
            $clone->entries[] = $entry;
        }
        return $clone;
    }

    /**
     * Creates the CAMT.052 Document.
     * 
     * @throws RuntimeException Wenn Pflichtfelder fehlen
     */
    public function build(): Document {
        if (!isset($this->id) || empty($this->id)) {
            throw new RuntimeException("Id muss angegeben werden.");
        }

        if (!isset($this->accountIdentifier) || empty($this->accountIdentifier)) {
            throw new RuntimeException("AccountIdentifier muss angegeben werden.");
        }

        if (!isset($this->currency)) {
            throw new RuntimeException("Currency muss angegeben werden.");
        }

        // Wenn nur Opening gegeben, berechne Closing
        $opening = $this->openingBalance;
        $closing = $this->closingBalance;

        if ($opening && !$closing) {
            $closing = $this->calculateClosingBalance($opening);
        } elseif (!$opening && $closing) {
            $opening = $this->reverseCalculateBalance($closing);
        }

        $document = new Document(
            $this->id,
            $this->creationDateTime,
            $this->accountIdentifier,
            $this->currency,
            $this->accountOwner,
            $this->servicerBic,
            $this->messageId,
            $this->sequenceNumber,
            $this->reportingSource,
            $opening,
            $closing
        );

        foreach ($this->entries as $entry) {
            $document->addEntry($entry);
        }

        return $document;
    }

    private function calculateClosingBalance(Balance $opening): Balance {
        $total = $opening->isDebit() ? -$opening->getAmount() : $opening->getAmount();

        foreach ($this->entries as $entry) {
            $sign = $entry->isCredit() ? 1 : -1;
            $total += $sign * $entry->getAmount();
        }

        $direction = $total >= 0 ? CreditDebit::CREDIT : CreditDebit::DEBIT;
        return new Balance($direction, $opening->getDate(), $opening->getCurrency(), abs($total), 'CLBD');
    }

    private function reverseCalculateBalance(Balance $closing): Balance {
        $total = $closing->isDebit() ? -$closing->getAmount() : $closing->getAmount();

        foreach ($this->entries as $entry) {
            $sign = $entry->isCredit() ? 1 : -1;
            $total -= $sign * $entry->getAmount();
        }

        $direction = $total >= 0 ? CreditDebit::CREDIT : CreditDebit::DEBIT;
        return new Balance($direction, $closing->getDate(), $closing->getCurrency(), abs($total), 'OPBD');
    }

    /**
     * Creates ein CAMT.052 Dokument aus einem MT940 Dokument.
     *
     * @param Mt940Document $mt940 Das MT940 Dokument
     * @param string|null $messageId Optionale Message-ID
     * @return Document Das konvertierte CAMT.052 Dokument
     */
    public static function fromMt940(Mt940Document $mt940, ?string $messageId = null): Document {
        return Mt940ToCamtConverter::toCamt052($mt940, $messageId);
    }

    /**
     * Creates ein CAMT.052 Dokument aus einem MT941 Dokument.
     *
     * @param Mt941Document $mt941 Das MT941 Dokument
     * @param string|null $messageId Optionale Message-ID
     * @return Document Das konvertierte CAMT.052 Dokument
     */
    public static function fromMt941(Mt941Document $mt941, ?string $messageId = null): Document {
        return Mt940ToCamtConverter::toCamt052(MtInterConverter::mt941ToMt940($mt941), $messageId);
    }

    /**
     * Creates ein CAMT.052 Dokument aus einem MT942 Dokument.
     *
     * @param Mt942Document $mt942 Das MT942 Dokument
     * @param string|null $messageId Optionale Message-ID
     * @return Document Das konvertierte CAMT.052 Dokument
     */
    public static function fromMt942(Mt942Document $mt942, ?string $messageId = null): Document {
        return Mt940ToCamtConverter::toCamt052(MtInterConverter::mt942ToMt940($mt942), $messageId);
    }
}
