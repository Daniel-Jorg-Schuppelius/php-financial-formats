<?php
/*
 * Created on   : Sun Jul 13 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Mt941DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\Mt;

use CommonToolkit\FinancialFormats\Converters\Banking\CamtToMt940Converter;
use CommonToolkit\FinancialFormats\Converters\Banking\MtInterConverter;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type52\Document as Camt052Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type53\Document as Camt053Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type54\Document as Camt054Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type940\Document as Mt940Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type941\Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Type942\Document as Mt942Document;
use CommonToolkit\FinancialFormats\Entities\Mt9\Balance;
use CommonToolkit\Enums\CreditDebit;
use DateTimeImmutable;
use RuntimeException;

/**
 * Builder für MT941 Documents (Balance Report).
 * 
 * MT941 enthält nur Saldeninformationen ohne Transaktionsdetails.
 * Verwendet für schnelle Saldenabfragen im Treasury/Cash Management.
 * 
 * @package CommonToolkit\Builders
 */
final class Mt941DocumentBuilder {
    private string $accountId;
    private string $referenceId = 'COMMON';
    private string $statementNumber = '00000';

    private ?Balance $openingBalance = null;
    private ?Balance $closingBalance = null;
    private ?Balance $closingAvailableBalance = null;

    /** @var Balance[] */
    private array $forwardAvailableBalances = [];

    private ?DateTimeImmutable $creationDateTime = null;

    public function setAccountId(string $accountId): self {
        $clone = clone $this;
        $clone->accountId = $accountId;
        return $clone;
    }

    public function setReferenceId(string $referenceId): self {
        $clone = clone $this;
        $clone->referenceId = $referenceId;
        return $clone;
    }

    public function setStatementNumber(string $statementNumber): self {
        $clone = clone $this;
        $clone->statementNumber = $statementNumber;
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

    public function setClosingAvailableBalance(Balance $balance): self {
        $clone = clone $this;
        $clone->closingAvailableBalance = $balance;
        return $clone;
    }

    public function addForwardAvailableBalance(Balance $balance): self {
        $clone = clone $this;
        $clone->forwardAvailableBalances[] = $balance;
        return $clone;
    }

    /**
     * Setzt mehrere Forward Available Balances.
     * 
     * @param Balance[] $balances
     */
    public function setForwardAvailableBalances(array $balances): self {
        $clone = clone $this;
        $clone->forwardAvailableBalances = $balances;
        return $clone;
    }

    public function setCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    /**
     * Erstellt das MT941 Document.
     * 
     * @throws RuntimeException Wenn accountId fehlt oder keine Salden angegeben sind
     */
    public function build(): Document {
        if (!isset($this->accountId) || empty($this->accountId)) {
            throw new RuntimeException("AccountId muss angegeben werden.");
        }

        if (!$this->openingBalance || !$this->closingBalance) {
            throw new RuntimeException("Opening- und Closing-Balance müssen für MT941 angegeben werden.");
        }

        return new Document(
            $this->accountId,
            $this->referenceId,
            $this->statementNumber,
            $this->openingBalance,
            $this->closingBalance,
            $this->closingAvailableBalance,
            $this->forwardAvailableBalances,
            $this->creationDateTime
        );
    }

    /**
     * Erstellt ein MT941 Dokument aus einem MT940 Dokument.
     *
     * @param Mt940Document $mt940 Das MT940 Dokument
     * @return Document Das konvertierte MT941 Dokument
     */
    public static function fromMt940(Mt940Document $mt940): Document {
        return MtInterConverter::mt940ToMt941($mt940);
    }

    /**
     * Erstellt ein MT941 Dokument aus einem MT942 Dokument.
     *
     * @param Mt942Document $mt942 Das MT942 Dokument
     * @return Document Das konvertierte MT941 Dokument
     */
    public static function fromMt942(Mt942Document $mt942): Document {
        return MtInterConverter::mt940ToMt941(MtInterConverter::mt942ToMt940($mt942));
    }

    /**
     * Erstellt ein MT941 Dokument aus einem CAMT.052 Dokument.
     *
     * @param Camt052Document $camt052 Das CAMT.052 Dokument
     * @return Document Das konvertierte MT941 Dokument
     */
    public static function fromCamt052(Camt052Document $camt052): Document {
        return MtInterConverter::mt940ToMt941(CamtToMt940Converter::fromCamt052($camt052));
    }

    /**
     * Erstellt ein MT941 Dokument aus einem CAMT.053 Dokument.
     *
     * @param Camt053Document $camt053 Das CAMT.053 Dokument
     * @return Document Das konvertierte MT941 Dokument
     */
    public static function fromCamt053(Camt053Document $camt053): Document {
        return MtInterConverter::mt940ToMt941(CamtToMt940Converter::fromCamt053($camt053));
    }

    /**
     * Erstellt ein MT941 Dokument aus einem CAMT.054 Dokument.
     *
     * @param Camt054Document $camt054 Das CAMT.054 Dokument
     * @return Document Das konvertierte MT941 Dokument
     */
    public static function fromCamt054(Camt054Document $camt054): Document {
        return MtInterConverter::mt940ToMt941(CamtToMt940Converter::fromCamt054($camt054));
    }
}
