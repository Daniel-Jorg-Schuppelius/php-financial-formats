<?php
/*
 * Created on   : Wed Jan 01 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain002DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\GroupHeader;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\OriginalGroupInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\OriginalPaymentInformation;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\TransactionInformationAndStatus;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\TransactionStatus;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\StatusReason;
use DateTimeImmutable;
use RuntimeException;

/**
 * Builder for pain.002 Documents (Customer Payment Status Report).
 * 
 * Creates status reports for payment orders according to ISO 20022.
 * Diese Nachricht wird typischerweise von Banken als Antwort auf pain.001/008 generiert.
 * 
 * Struktur:
 * - GroupHeader: Nachrichten-Metadaten
 * - OriginalGroupInformation: Referenz zur Original-Nachricht
 * - OriginalPaymentInformation[]: Status einzelner Payment Instructions
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Pain
 */
final class Pain002DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private ?PartyIdentification $initiatingParty = null;
    private ?OriginalGroupInformation $originalGroupInformation = null;

    /** @var OriginalPaymentInformation[] */
    private array $originalPaymentInformations = [];

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
     * Setzt die initiierende Partei (optional).
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
     * Convenience: Setzt Referenz auf eine pain.001 Nachricht.
     */
    public function forPain001(
        string $originalMessageId,
        ?TransactionStatus $groupStatus = null
    ): self {
        $clone = clone $this;
        $clone->originalGroupInformation = OriginalGroupInformation::forPain001(
            $originalMessageId,
            $groupStatus
        );
        return $clone;
    }

    /**
     * Convenience: Setzt Referenz auf eine pain.008 Nachricht.
     */
    public function forPain008(
        string $originalMessageId,
        ?TransactionStatus $groupStatus = null
    ): self {
        $clone = clone $this;
        $clone->originalGroupInformation = OriginalGroupInformation::forPain008(
            $originalMessageId,
            $groupStatus
        );
        return $clone;
    }

    /**
     * Adds Payment Information Status.
     */
    public function addOriginalPaymentInformation(OriginalPaymentInformation $info): self {
        $clone = clone $this;
        $clone->originalPaymentInformations[] = $info;
        return $clone;
    }

    /**
     * Adds a rejected Payment Information Status.
     */
    public function addRejectedPaymentInformation(
        string $originalPaymentInformationId,
        TransactionStatus $status,
        ?string $reasonCode = null,
        ?string $additionalInfo = null
    ): self {
        $statusReasons = [];
        if ($reasonCode !== null || $additionalInfo !== null) {
            $statusReasons[] = new StatusReason(
                code: $reasonCode,
                additionalInfo: $additionalInfo !== null ? [$additionalInfo] : []
            );
        }

        $info = new OriginalPaymentInformation(
            originalPaymentInformationId: $originalPaymentInformationId,
            status: $status,
            statusReasons: $statusReasons
        );

        return $this->addOriginalPaymentInformation($info);
    }

    /**
     * Adds an accepted Payment Information Status.
     */
    public function addAcceptedPaymentInformation(string $originalPaymentInformationId): self {
        $info = new OriginalPaymentInformation(
            originalPaymentInformationId: $originalPaymentInformationId,
            status: TransactionStatus::ACCEPTED_SETTLEMENT_COMPLETED
        );

        return $this->addOriginalPaymentInformation($info);
    }

    /**
     * Erstellt das pain.002 Document.
     * 
     * @throws RuntimeException Wenn Pflichtfelder fehlen
     */
    public function build(): Document {
        if (!isset($this->messageId) || empty($this->messageId)) {
            throw new RuntimeException("MessageId muss angegeben werden.");
        }

        if ($this->originalGroupInformation === null) {
            throw new RuntimeException("OriginalGroupInformation muss angegeben werden.");
        }

        $groupHeader = GroupHeader::create($this->messageId, $this->initiatingParty);

        return new Document(
            $groupHeader,
            $this->originalGroupInformation,
            $this->originalPaymentInformations
        );
    }

    /**
     * Creates a fully accepted status-Report.
     */
    public static function createAllAccepted(
        string $messageId,
        string $originalMessageId,
        string $originalMessageName = 'pain.001.001.12'
    ): Document {
        return Document::allAccepted($messageId, $originalMessageId, $originalMessageName);
    }

    /**
     * Creates a completely rejected status report.
     */
    public static function createRejected(
        string $messageId,
        string $originalMessageId,
        string $reasonCode,
        ?string $additionalInfo = null,
        string $originalMessageName = 'pain.001.001.12'
    ): Document {
        $statusReasons = [
            new StatusReason(
                code: $reasonCode,
                additionalInfo: $additionalInfo !== null ? [$additionalInfo] : []
            )
        ];

        return new Document(
            groupHeader: GroupHeader::create($messageId),
            originalGroupInformation: new OriginalGroupInformation(
                originalMessageId: $originalMessageId,
                originalMessageNameId: $originalMessageName,
                groupStatus: TransactionStatus::REJECTED,
                statusReasons: $statusReasons
            )
        );
    }
}
