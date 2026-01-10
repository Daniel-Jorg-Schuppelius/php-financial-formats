<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DocumentAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\Mt9;

use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * Abstract base class for MT9xx documents (SWIFT Cash Management).
 * 
 * Gemeinsame Eigenschaften aller MT9-Nachrichtentypen:
 * - Account-Informationen
 * - Referenz-ID
 * - Auszugsnummer
 * - Currency
 * 
 * @package CommonToolkit\Contracts\Abstracts\Common\Banking\Mt9
 */
abstract class DocumentAbstract {
    protected string $accountId;
    protected string $referenceId;
    protected ?string $relatedReference;
    protected string $statementNumber;
    protected CurrencyCode $currency;
    protected DateTimeImmutable $creationDateTime;

    public function __construct(
        string $accountId,
        string $referenceId,
        string $statementNumber,
        CurrencyCode $currency,
        ?DateTimeImmutable $creationDateTime = null,
        ?string $relatedReference = null
    ) {
        $this->accountId = $accountId;
        $this->referenceId = $referenceId;
        $this->relatedReference = $relatedReference;
        $this->statementNumber = $statementNumber;
        $this->currency = $currency;
        $this->creationDateTime = $creationDateTime ?? new DateTimeImmutable();
    }

    /**
     * Returns the MT message type.
     */
    abstract public function getMtType(): MtType;

    /**
     * Returns the account number/IBAN.
     * Feld :25: in SWIFT-Notation.
     */
    public function getAccountId(): string {
        return $this->accountId;
    }

    /**
     * Returns the transaction reference.
     * Feld :20: in SWIFT-Notation.
     */
    public function getReferenceId(): string {
        return $this->referenceId;
    }

    /**
     * Returns the related reference (optional).
     * Feld :21: in SWIFT-Notation.
     * Contains the field 20 of the MT 920 request message if this statement was requested.
     */
    public function getRelatedReference(): ?string {
        return $this->relatedReference;
    }

    /**
     * Returns the statement number/sequence number.
     * Feld :28C: in SWIFT-Notation.
     */
    public function getStatementNumber(): string {
        return $this->statementNumber;
    }

    /**
     * Returns the currency.
     */
    public function getCurrency(): CurrencyCode {
        return $this->currency;
    }

    /**
     * Returns the creation date.
     */
    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    /**
     * Returns the number of entries.
     */
    abstract public function countEntries(): int;

    /**
     * Serialisiert das Dokument im SWIFT MT-Format.
     */
    abstract public function __toString(): string;
}
