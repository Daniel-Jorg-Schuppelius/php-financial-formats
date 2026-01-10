<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\Pain\DocumentAbstract;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\PainType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\TransactionStatus;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain002Generator;
use DateTimeImmutable;

/**
 * pain.002 Document - Customer Payment Status Report.
 * 
 * Status feedback from the bank on submitted payment orders.
 * Response to pain.001 (credit transfers) or pain.008 (direct debits).
 * 
 * Struktur:
 * - CstmrPmtStsRpt (Customer Payment Status Report)
 *   - GrpHdr: Nachrichten-Header
 *   - OrgnlGrpInfAndSts: Status der Original-Nachricht
 *   - OrgnlPmtInfAndSts[]: Status einzelner Payment Instructions
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type2
 */
final class Document extends DocumentAbstract {
    /** @var OriginalPaymentInformation[] */
    private array $originalPaymentInformations = [];

    public function __construct(
        private GroupHeader $groupHeader,
        private OriginalGroupInformation $originalGroupInformation,
        array $originalPaymentInformations = []
    ) {
        $this->originalPaymentInformations = $originalPaymentInformations;
    }

    /**
     * Factory-Methode.
     */
    public static function create(
        string $messageId,
        OriginalGroupInformation $originalGroupInfo,
        array $originalPaymentInformations = [],
        ?PartyIdentification $initiatingParty = null
    ): self {
        return new self(
            groupHeader: GroupHeader::create($messageId, $initiatingParty),
            originalGroupInformation: $originalGroupInfo,
            originalPaymentInformations: $originalPaymentInformations
        );
    }

    /**
     * Creates a fully accepted status.
     */
    public static function allAccepted(
        string $messageId,
        string $originalMessageId,
        string $originalMessageName = 'pain.001.001.12'
    ): self {
        return new self(
            groupHeader: GroupHeader::create($messageId),
            originalGroupInformation: new OriginalGroupInformation(
                originalMessageId: $originalMessageId,
                originalMessageNameId: $originalMessageName,
                groupStatus: TransactionStatus::ACCEPTED_SETTLEMENT_COMPLETED
            )
        );
    }

    /**
     * Returns the message type.
     */
    public function getType(): PainType {
        return PainType::PAIN_002;
    }

    public function getGroupHeader(): GroupHeader {
        return $this->groupHeader;
    }

    public function getOriginalGroupInformation(): OriginalGroupInformation {
        return $this->originalGroupInformation;
    }

    /**
     * @return OriginalPaymentInformation[]
     */
    public function getOriginalPaymentInformations(): array {
        return $this->originalPaymentInformations;
    }

    /**
     * Adds a payment information entry.
     */
    public function addOriginalPaymentInformation(OriginalPaymentInformation $info): self {
        $clone = clone $this;
        $clone->originalPaymentInformations[] = $info;
        return $clone;
    }

    /**
     * Checks if all transactions were accepted.
     */
    public function isFullyAccepted(): bool {
        // Gruppen-Status prüfen
        if ($this->originalGroupInformation->isGroupAccepted()) {
            return true;
        }

        if ($this->originalGroupInformation->isGroupRejected()) {
            return false;
        }

        // Einzelne Payment Informations prüfen
        foreach ($this->originalPaymentInformations as $pmtInfo) {
            if (!$pmtInfo->isFullyAccepted()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if there are rejections.
     */
    public function hasRejections(): bool {
        if ($this->originalGroupInformation->isGroupRejected()) {
            return true;
        }

        foreach ($this->originalPaymentInformations as $pmtInfo) {
            if ($pmtInfo->hasRejections()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns all rejected transactions.
     */
    public function getRejectedTransactions(): array {
        $rejected = [];

        foreach ($this->originalPaymentInformations as $pmtInfo) {
            $rejected = array_merge($rejected, $pmtInfo->getRejectedTransactions());
        }

        return $rejected;
    }

    /**
     * Counts all transaction statuses.
     */
    public function countTransactionStatuses(): int {
        $count = 0;

        foreach ($this->originalPaymentInformations as $pmtInfo) {
            $count += $pmtInfo->countTransactionStatuses();
        }

        return $count;
    }

    /**
     * Validiert das Dokument.
     * 
     * @return array{valid: bool, errors: string[]}
     */
    public function validate(): array {
        $errors = [];

        if (strlen($this->groupHeader->getMessageId()) > 35) {
            $errors[] = 'MsgId must not exceed 35 characters';
        }

        if (strlen($this->originalGroupInformation->getOriginalMessageId()) > 35) {
            $errors[] = 'OrgnlMsgId must not exceed 35 characters';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Generates XML output for this document.
     *
     * @param string|null $namespace Optionaler XML-Namespace
     * @return string Das generierte XML
     */
    public function toXml(?string $namespace = null): string {
        $generator = $namespace !== null
            ? new Pain002Generator($namespace)
            : new Pain002Generator();
        return $generator->generate($this);
    }
}
