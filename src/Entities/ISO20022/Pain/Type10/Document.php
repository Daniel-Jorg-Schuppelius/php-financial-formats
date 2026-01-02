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

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type10;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Enums\PainType;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain010Generator;
use DateTimeImmutable;

/**
 * pain.010 Document - Mandate Amendment Request.
 * 
 * Anfrage zur Änderung eines bestehenden SEPA-Lastschrift-Mandats.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type10
 */
final class Document {
    /** @var MandateAmendment[] */
    private array $mandateAmendments = [];

    public function __construct(
        private readonly string $messageId,
        private readonly DateTimeImmutable $creationDateTime,
        private readonly PartyIdentification $initiatingParty,
        array $mandateAmendments = []
    ) {
        $this->mandateAmendments = $mandateAmendments;
    }

    public static function create(
        string $messageId,
        PartyIdentification $initiatingParty,
        array $mandateAmendments = []
    ): self {
        return new self(
            messageId: $messageId,
            creationDateTime: new DateTimeImmutable(),
            initiatingParty: $initiatingParty,
            mandateAmendments: $mandateAmendments
        );
    }

    public function getType(): PainType {
        return PainType::PAIN_010;
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
     * @return MandateAmendment[]
     */
    public function getMandateAmendments(): array {
        return $this->mandateAmendments;
    }

    public function addMandateAmendment(MandateAmendment $amendment): self {
        $clone = clone $this;
        $clone->mandateAmendments[] = $amendment;
        return $clone;
    }

    public function countAmendments(): int {
        return count($this->mandateAmendments);
    }

    /**
     * @return array{valid: bool, errors: string[]}
     */
    public function validate(): array {
        $errors = [];

        if (strlen($this->messageId) > 35) {
            $errors[] = 'MsgId darf maximal 35 Zeichen lang sein';
        }

        if (empty($this->mandateAmendments)) {
            $errors[] = 'Mindestens eine Mandatsänderung erforderlich';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Generiert XML-Ausgabe für dieses Dokument.
     *
     * @param string|null $namespace Optionaler XML-Namespace
     * @return string Das generierte XML
     */
    public function toXml(?string $namespace = null): string {
        $generator = $namespace !== null
            ? new Pain010Generator($namespace)
            : new Pain010Generator();
        return $generator->generate($this);
    }
}
