<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Camt054DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Converters\Banking\MtInterConverter;
use CommonToolkit\FinancialFormats\Converters\Banking\Mt940ToCamtConverter;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54\Transaction;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type940\Document as Mt940Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type941\Document as Mt941Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type942\Document as Mt942Document;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

/**
 * Builder für CAMT.054 Documents (Bank to Customer Debit Credit Notification).
 * 
 * CAMT.054 enthält Einzelumsatzbenachrichtigungen ohne Salden.
 * Verwendet für Echtzeit-Buchungsbenachrichtigungen.
 * 
 * @package CommonToolkit\Builders
 */
final class Camt054DocumentBuilder {
    private string $id;
    private DateTimeImmutable $creationDateTime;
    private string $accountIdentifier;
    private CurrencyCode $currency;
    private ?string $accountOwner = null;
    private ?string $servicerBic = null;
    private ?string $messageId = null;
    private ?string $sequenceNumber = null;

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

    public function addEntry(Transaction $entry): self {
        $clone = clone $this;
        $clone->entries[] = $entry;
        return $clone;
    }

    /**
     * Fügt mehrere Transaktionen hinzu.
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
     * Erstellt das CAMT.054 Document.
     * 
     * CAMT.054 enthält typischerweise mindestens einen Eintrag.
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

        $document = new Document(
            $this->id,
            $this->creationDateTime,
            $this->accountIdentifier,
            $this->currency,
            $this->accountOwner,
            $this->servicerBic,
            $this->messageId,
            $this->sequenceNumber
        );

        foreach ($this->entries as $entry) {
            $document->addEntry($entry);
        }

        return $document;
    }

    /**
     * Erstellt ein CAMT.054 Dokument aus einem MT940 Dokument.
     *
     * @param Mt940Document $mt940 Das MT940 Dokument
     * @param string|null $messageId Optionale Message-ID
     * @return Document Das konvertierte CAMT.054 Dokument
     */
    public static function fromMt940(Mt940Document $mt940, ?string $messageId = null): Document {
        return Mt940ToCamtConverter::toCamt054($mt940, $messageId);
    }

    /**
     * Erstellt ein CAMT.054 Dokument aus einem MT941 Dokument.
     *
     * @param Mt941Document $mt941 Das MT941 Dokument
     * @param string|null $messageId Optionale Message-ID
     * @return Document Das konvertierte CAMT.054 Dokument
     */
    public static function fromMt941(Mt941Document $mt941, ?string $messageId = null): Document {
        return Mt940ToCamtConverter::toCamt054(MtInterConverter::mt941ToMt940($mt941), $messageId);
    }

    /**
     * Erstellt ein CAMT.054 Dokument aus einem MT942 Dokument.
     *
     * @param Mt942Document $mt942 Das MT942 Dokument
     * @param string|null $messageId Optionale Message-ID
     * @return Document Das konvertierte CAMT.054 Dokument
     */
    public static function fromMt942(Mt942Document $mt942, ?string $messageId = null): Document {
        return Mt940ToCamtConverter::toCamt054(MtInterConverter::mt942ToMt940($mt942), $messageId);
    }
}
