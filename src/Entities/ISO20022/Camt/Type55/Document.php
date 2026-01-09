<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\Type55;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Camt\Camt055Generator;
use CommonToolkit\FinancialFormats\Traits\XmlDocumentExportTrait;
use DateTimeImmutable;

/**
 * CAMT.055 Document (Customer Payment Cancellation Request).
 * 
 * Represents a cancellation request from customer to bank
 * according to ISO 20022 camt.055.001.xx Standard.
 * 
 * Uses <CstmrPmtCxlReq> as root and <Undrlyg> for the underlying transactions.
 * 
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type55
 */
class Document implements CamtDocumentInterface {
    use XmlDocumentExportTrait;

    protected string $messageId;
    protected DateTimeImmutable $creationDateTime;
    protected ?string $numberOfTransactions = null;
    protected ?float $controlSum = null;
    protected ?string $initiatingPartyName = null;
    protected ?string $initiatingPartyId = null;
    protected ?string $caseId = null;
    protected ?string $caseCreator = null;

    /** @var UnderlyingTransaction[] */
    protected array $underlyingTransactions = [];

    public function __construct(
        string $messageId,
        DateTimeImmutable|string $creationDateTime,
        ?string $numberOfTransactions = null,
        float|string|null $controlSum = null,
        ?string $initiatingPartyName = null,
        ?string $initiatingPartyId = null,
        ?string $caseId = null,
        ?string $caseCreator = null
    ) {
        $this->messageId = $messageId;
        $this->creationDateTime = $creationDateTime instanceof DateTimeImmutable
            ? $creationDateTime
            : new DateTimeImmutable($creationDateTime);
        $this->numberOfTransactions = $numberOfTransactions;
        $this->controlSum = is_string($controlSum) ? (float) $controlSum : $controlSum;
        $this->initiatingPartyName = $initiatingPartyName;
        $this->initiatingPartyId = $initiatingPartyId;
        $this->caseId = $caseId;
        $this->caseCreator = $caseCreator;
    }

    public function getCamtType(): CamtType {
        return CamtType::CAMT055;
    }

    public function getMessageId(): string {
        return $this->messageId;
    }

    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    public function getNumberOfTransactions(): ?string {
        return $this->numberOfTransactions;
    }

    public function getControlSum(): ?float {
        return $this->controlSum;
    }

    public function getInitiatingPartyName(): ?string {
        return $this->initiatingPartyName;
    }

    public function getInitiatingPartyId(): ?string {
        return $this->initiatingPartyId;
    }

    public function getCaseId(): ?string {
        return $this->caseId;
    }

    public function getCaseCreator(): ?string {
        return $this->caseCreator;
    }

    public function addUnderlyingTransaction(UnderlyingTransaction $underlying): void {
        $this->underlyingTransactions[] = $underlying;
    }

    /**
     * @return UnderlyingTransaction[]
     */
    public function getUnderlyingTransactions(): array {
        return $this->underlyingTransactions;
    }

    /**
     * Alle PaymentCancellationRequests aus allen UnderlyingTransactions.
     * 
     * @return PaymentCancellationRequest[]
     */
    public function getAllCancellationRequests(): array {
        $requests = [];
        foreach ($this->underlyingTransactions as $underlying) {
            // Direkte TxInf
            foreach ($underlying->getTransactionInformation() as $txInfo) {
                $requests[] = $txInfo;
            }
            // Via OrgnlPmtInfAndCxl
            foreach ($underlying->getOriginalPaymentInformationAndCancellation() as $pmtInf) {
                foreach ($pmtInf->getTransactionInformation() as $txInfo) {
                    $requests[] = $txInfo;
                }
            }
        }
        return $requests;
    }

    public function getTotalTransactionCount(): int {
        $count = 0;
        foreach ($this->underlyingTransactions as $underlying) {
            $count += $underlying->getTotalTransactionCount();
        }
        return $count;
    }

    protected function getDefaultXml(): string {
        return $this->toXml();
    }

    public function toXml(CamtVersion $version = CamtVersion::V12): string {
        return (new Camt055Generator())->generate($this, $version);
    }
}
