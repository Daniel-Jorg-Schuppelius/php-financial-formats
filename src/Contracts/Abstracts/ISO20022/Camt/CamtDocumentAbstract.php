<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CamtDocumentAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt;

use CommonToolkit\Contracts\Abstracts\XML\DomainXmlDocumentAbstract;
use CommonToolkit\Entities\XML\Document as XmlDocument;
use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Abstract base class for all CAMT documents (052, 053, 054).
 * 
 * Contains common properties and methods for:
 * - CAMT.052: BankToCustomerAccountReport (Intraday)
 * - CAMT.053: BankToCustomerStatement (End of Day)
 * - CAMT.054: BankToCustomerDebitCreditNotification
 * 
 * Inherits from DomainXmlDocumentAbstract for XmlDocumentInterface implementation.
 * 
 * @package CommonToolkit\Entities\Common\Banking
 */
abstract class CamtDocumentAbstract extends DomainXmlDocumentAbstract implements CamtDocumentInterface {
    protected string $id;
    protected DateTimeImmutable $creationDateTime;
    protected string $accountIdentifier;
    protected CurrencyCode $currency;
    protected ?string $accountOwner;
    protected ?string $servicerBic;
    protected ?string $messageId;
    protected ?string $sequenceNumber;

    // Pagination support
    protected ?int $pageNumber = null;
    protected ?bool $lastPageIndicator = null;

    /** @var CamtTransactionAbstract[] */
    protected array $entries = [];

    /**
     * @param string $id Statement/Report/Notification ID
     * @param DateTimeImmutable|string $creationDateTime Erstellungszeitpunkt
     * @param string $accountIdentifier IBAN oder andere Kontoidentifikation
     * @param CurrencyCode|string $currency Account currency
     * @param string|null $accountOwner Kontoinhaber
     * @param string|null $servicerBic BIC of the account-holding bank
     * @param string|null $messageId Nachrichten-ID (aus GrpHdr)
     * @param string|null $sequenceNumber Sequenznummer
     */
    public function __construct(
        string $id,
        DateTimeImmutable|string $creationDateTime,
        string $accountIdentifier,
        CurrencyCode|string $currency,
        ?string $accountOwner = null,
        ?string $servicerBic = null,
        ?string $messageId = null,
        ?string $sequenceNumber = null
    ) {
        $this->id = $id;

        $this->creationDateTime = $creationDateTime instanceof DateTimeImmutable
            ? $creationDateTime
            : new DateTimeImmutable($creationDateTime);

        $this->accountIdentifier = $accountIdentifier;

        $this->currency = $currency instanceof CurrencyCode
            ? $currency
            : CurrencyCode::tryFrom(strtoupper($currency))
            ?? throw new InvalidArgumentException("Ungültige Währung: $currency");

        $this->accountOwner = $accountOwner;
        $this->servicerBic = $servicerBic;
        $this->messageId = $messageId;
        $this->sequenceNumber = $sequenceNumber;
    }

    /**
     * Returns the CAMT type of this document.
     */
    abstract public function getCamtType(): CamtType;

    public function getId(): string {
        return $this->id;
    }

    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    public function getAccountIdentifier(): string {
        return $this->accountIdentifier;
    }

    /**
     * Returns the IBAN (alias for getAccountIdentifier for IBAN accounts).
     */
    public function getAccountIban(): string {
        return $this->accountIdentifier;
    }

    public function getCurrency(): CurrencyCode {
        return $this->currency;
    }

    public function getAccountOwner(): ?string {
        return $this->accountOwner;
    }

    public function getServicerBic(): ?string {
        return $this->servicerBic;
    }

    public function getMessageId(): ?string {
        return $this->messageId;
    }

    public function getSequenceNumber(): ?string {
        return $this->sequenceNumber;
    }

    public function getPageNumber(): ?int {
        return $this->pageNumber;
    }

    public function setPageNumber(?int $pageNumber): static {
        $this->pageNumber = $pageNumber;
        return $this;
    }

    public function isLastPage(): ?bool {
        return $this->lastPageIndicator;
    }

    public function setLastPageIndicator(?bool $lastPageIndicator): static {
        $this->lastPageIndicator = $lastPageIndicator;
        return $this;
    }

    /**
     * Sets pagination in one call.
     */
    public function setPagination(int $pageNumber, bool $isLastPage): static {
        $this->pageNumber = $pageNumber;
        $this->lastPageIndicator = $isLastPage;
        return $this;
    }

    /**
     * @return CamtTransactionAbstract[]
     */
    public function getEntries(): array {
        return $this->entries;
    }

    public function countEntries(): int {
        return count($this->entries);
    }

    /**
     * Berechnet die Summe aller Haben-Buchungen.
     */
    public function getTotalCredits(): float {
        $sum = 0.0;
        foreach ($this->entries as $entry) {
            if ($entry->isCredit()) {
                $sum += $entry->getAmount();
            }
        }
        return round($sum, 2);
    }

    /**
     * Berechnet die Summe aller Soll-Buchungen.
     */
    public function getTotalDebits(): float {
        $sum = 0.0;
        foreach ($this->entries as $entry) {
            if ($entry->isDebit()) {
                $sum += $entry->getAmount();
            }
        }
        return round($sum, 2);
    }

    /**
     * Berechnet den Netto-Umsatz (Haben - Soll).
     */
    public function getNetAmount(): float {
        return round($this->getTotalCredits() - $this->getTotalDebits(), 2);
    }

    /**
     * Generates XML output for this document.
     */
    abstract public function toXml(CamtVersion $version = CamtVersion::V02): string;

    // =========================================================================
    // DomainXmlDocumentAbstract Implementation
    // =========================================================================

    /**
     * Version for the cached document.
     */
    private ?CamtVersion $cachedVersion = null;

    /**
     * @inheritDoc
     */
    protected function getDefaultXml(): string {
        return $this->toXml();
    }

    /**
     * Returns the document as CommonToolkit XmlDocument.
     * 
     * Overrides the base implementation to enable version-based caching.
     * 
     * @param CamtVersion $version CAMT version for XML generation
     */
    public function toXmlDocument(CamtVersion $version = CamtVersion::V02): XmlDocument {
        if ($this->cachedVersion !== $version) {
            $this->invalidateCache();
            $this->cachedVersion = $version;
        }

        // Temporär die Version setzen für getDefaultXml()
        $originalVersion = $this->cachedVersion;
        $xml = $this->toXml($version);

        return XmlDocument::fromString($xml);
    }

    /**
     * @inheritDoc
     */
    protected function invalidateCache(): void {
        parent::invalidateCache();
        $this->cachedVersion = null;
    }
}
