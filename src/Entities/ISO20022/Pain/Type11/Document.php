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

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type11;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Enums\PainType;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain011Generator;
use DateTimeImmutable;

/**
 * pain.011 Document - Mandate Cancellation Request.
 * 
 * Request to cancel/terminate a SEPA direct debit mandate.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type11
 */
final class Document {
    /** @var MandateCancellation[] */
    private array $mandateCancellations = [];

    public function __construct(
        private readonly string $messageId,
        private readonly DateTimeImmutable $creationDateTime,
        private readonly PartyIdentification $initiatingParty,
        array $mandateCancellations = []
    ) {
        $this->mandateCancellations = $mandateCancellations;
    }

    public static function create(
        string $messageId,
        PartyIdentification $initiatingParty,
        array $mandateCancellations = []
    ): self {
        return new self(
            messageId: $messageId,
            creationDateTime: new DateTimeImmutable(),
            initiatingParty: $initiatingParty,
            mandateCancellations: $mandateCancellations
        );
    }

    public function getType(): PainType {
        return PainType::PAIN_011;
    }

    public function getMessageId(): string {
        return $this->messageId;
    }

    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    public function getInitiatingParty(): PartyIdentification {
        return $this->initiatingParty;
    }

    /**
     * @return MandateCancellation[]
     */
    public function getMandateCancellations(): array {
        return $this->mandateCancellations;
    }

    public function addMandateCancellation(MandateCancellation $cancellation): self {
        $clone = clone $this;
        $clone->mandateCancellations[] = $cancellation;
        return $clone;
    }

    public function countCancellations(): int {
        return count($this->mandateCancellations);
    }

    /**
     * @return array{valid: bool, errors: string[]}
     */
    public function validate(): array {
        $errors = [];

        if (strlen($this->messageId) > 35) {
            $errors[] = 'MsgId must not exceed 35 characters';
        }

        if (empty($this->mandateCancellations)) {
            $errors[] = 'Mindestens eine Mandatskündigung erforderlich';
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
            ? new Pain011Generator($namespace)
            : new Pain011Generator();
        return $generator->generate($this);
    }
}
