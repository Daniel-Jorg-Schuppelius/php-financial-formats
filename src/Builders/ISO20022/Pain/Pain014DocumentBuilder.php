<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain014DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type2\StatusReason;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type14\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type14\PaymentActivationStatus;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder for pain.014 Documents (Creditor Payment Activation Request Status Report).
 * 
 * Creates status reports for payment activation requests.
 * Wird typischerweise von Banken als Antwort auf pain.013 generiert.
 * 
 * Verwendung:
 * ```php
 * $document = Pain014DocumentBuilder::forPain013('MSG-001', 'ORIG-MSG-001')
 *     ->addAccepted('INST-001', 'E2E-001')
 *     ->addPending('INST-002', 'E2E-002')
 *     ->addRejected('INST-003', 'E2E-003', StatusReason::insufficientFunds())
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Pain
 */
final class Pain014DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private string $originalMessageId;
    private ?PartyIdentification $initiatingParty = null;
    /** @var PaymentActivationStatus[] */
    private array $paymentStatuses = [];

    private function __construct(string $messageId, string $originalMessageId) {
        if (strlen($messageId) > 35) {
            throw new InvalidArgumentException('MsgId must not exceed 35 characters');
        }
        $this->messageId = $messageId;
        $this->creationDateTime = new DateTimeImmutable();
        $this->originalMessageId = $originalMessageId;
    }

    /**
     * Creates builder for pain.013 response.
     */
    public static function forPain013(string $messageId, string $originalMessageId): self {
        return new self($messageId, $originalMessageId);
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
     * Setzt die initiierende Partei (optional).
     */
    public function withInitiatingParty(PartyIdentification $party): self {
        $clone = clone $this;
        $clone->initiatingParty = $party;
        return $clone;
    }

    /**
     * Adds an accepted status.
     */
    public function addAccepted(string $originalInstructionId, string $originalEndToEndId): self {
        $clone = clone $this;
        $clone->paymentStatuses[] = PaymentActivationStatus::accepted($originalInstructionId, $originalEndToEndId);
        return $clone;
    }

    /**
     * Adds a pending status.
     */
    public function addPending(string $originalInstructionId, string $originalEndToEndId): self {
        $clone = clone $this;
        $clone->paymentStatuses[] = PaymentActivationStatus::pending($originalInstructionId, $originalEndToEndId);
        return $clone;
    }

    /**
     * Adds a rejected status.
     */
    public function addRejected(
        string $originalInstructionId,
        string $originalEndToEndId,
        StatusReason $reason
    ): self {
        $clone = clone $this;
        $clone->paymentStatuses[] = PaymentActivationStatus::rejected(
            $originalInstructionId,
            $originalEndToEndId,
            $reason
        );
        return $clone;
    }

    /**
     * Adds a completed PaymentActivationStatus.
     */
    public function addPaymentStatus(PaymentActivationStatus $status): self {
        $clone = clone $this;
        $clone->paymentStatuses[] = $status;
        return $clone;
    }

    /**
     * Adds multiple payment statuses.
     * 
     * @param PaymentActivationStatus[] $statuses
     */
    public function addPaymentStatuses(array $statuses): self {
        $clone = clone $this;
        $clone->paymentStatuses = array_merge($clone->paymentStatuses, $statuses);
        return $clone;
    }

    /**
     * Erstellt das pain.014 Dokument.
     * 
     * @throws InvalidArgumentException wenn keine Statuses vorhanden
     */
    public function build(): Document {
        if (empty($this->paymentStatuses)) {
            throw new InvalidArgumentException('Mindestens ein Zahlungsstatus erforderlich');
        }

        return Document::create(
            messageId: $this->messageId,
            originalMessageId: $this->originalMessageId,
            paymentStatuses: $this->paymentStatuses,
            initiatingParty: $this->initiatingParty
        );
    }

    // === Static Factory Methods ===

    /**
     * Erstellt einen einfachen Akzeptiert-Status.
     */
    public static function createSingleAccepted(
        string $messageId,
        string $originalMessageId,
        string $originalInstructionId,
        string $originalEndToEndId
    ): Document {
        return self::forPain013($messageId, $originalMessageId)
            ->addAccepted($originalInstructionId, $originalEndToEndId)
            ->build();
    }

    /**
     * Erstellt einen einfachen Abgelehnt-Status.
     */
    public static function createSingleRejected(
        string $messageId,
        string $originalMessageId,
        string $originalInstructionId,
        string $originalEndToEndId,
        StatusReason $reason
    ): Document {
        return self::forPain013($messageId, $originalMessageId)
            ->addRejected($originalInstructionId, $originalEndToEndId, $reason)
            ->build();
    }

    /**
     * Akzeptiert alle Zahlungen aus einer Liste.
     * 
     * @param array<array{instructionId: string, endToEndId: string}> $payments
     */
    public static function acceptAll(
        string $messageId,
        string $originalMessageId,
        array $payments
    ): Document {
        $builder = self::forPain013($messageId, $originalMessageId);

        foreach ($payments as $payment) {
            $builder = $builder->addAccepted($payment['instructionId'], $payment['endToEndId']);
        }

        return $builder->build();
    }
}
