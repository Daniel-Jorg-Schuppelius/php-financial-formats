<?php
/*
 * Created on   : Thu May 08 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Balance;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Camt\CamtDocumentAbstract;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt053Generator;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * CAMT.053 Document (Bank to Customer Statement).
 * 
 * Represents a daily statement according to ISO 20022 camt.053.001.02/08 Standard.
 * 
 * Uses <BkToCstmrStmt> as root and <Stmt> for statements.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Camt053
 */
class Document extends CamtDocumentAbstract {
    private ?Balance $openingBalance = null;
    private ?Balance $closingBalance = null;

    /** @var Transaction[] */
    protected array $entries = [];

    public function __construct(
        string $id,
        DateTimeImmutable|string $creationDateTime,
        string $accountIdentifier,
        CurrencyCode|string $currency,
        ?string $accountOwner = null,
        ?string $servicerBic = null,
        ?string $messageId = null,
        ?string $sequenceNumber = null,
        ?Balance $openingBalance = null,
        ?Balance $closingBalance = null
    ) {
        parent::__construct(
            $id,
            $creationDateTime,
            $accountIdentifier,
            $currency,
            $accountOwner,
            $servicerBic,
            $messageId,
            $sequenceNumber
        );

        $this->openingBalance = $openingBalance;
        $this->closingBalance = $closingBalance;
    }

    public function getCamtType(): CamtType {
        return CamtType::CAMT053;
    }

    public function getOpeningBalance(): ?Balance {
        return $this->openingBalance;
    }

    public function getClosingBalance(): ?Balance {
        return $this->closingBalance;
    }

    public function addEntry(Transaction $entry): void {
        $this->entries[] = $entry;
    }

    /**
     * @return Transaction[]
     */
    public function getEntries(): array {
        return $this->entries;
    }

    public function withOpeningBalance(Balance $balance): self {
        $clone = clone $this;
        $clone->openingBalance = $balance;
        return $clone;
    }

    public function withClosingBalance(Balance $balance): self {
        $clone = clone $this;
        $clone->closingBalance = $balance;
        return $clone;
    }

    public function toXml(CamtVersion $version = CamtVersion::V02): string {
        return (new Camt053Generator())->generate($this, $version);
    }

    /**
     * Returns the document as XML string.
     */
    public function __toString(): string {
        return $this->toXml();
    }
}
