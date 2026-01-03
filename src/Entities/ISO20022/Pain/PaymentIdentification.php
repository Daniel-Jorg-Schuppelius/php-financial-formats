<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PaymentIdentification.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain;

/**
 * Payment identification for pain messages.
 * 
 * Contains the references for payment identification:
 * - InstrId: Instruction Identification (optional, intern)
 * - EndToEndId: End-to-End Identification (required, end-to-end)
 * - UETR: Unique End-to-End Transaction Reference (optional, UUID)
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain
 */
final readonly class PaymentIdentification {
    public function __construct(
        private string $endToEndId,
        private ?string $instructionId = null,
        private ?string $uetr = null
    ) {
    }

    /**
     * Returns the end-to-end ID (EndToEndId).
     * This is the continuous reference that is transported to the recipient.
     */
    public function getEndToEndId(): string {
        return $this->endToEndId;
    }

    /**
     * Returns the instruction ID (InstrId).
     * Dies ist die interne Referenz zwischen Auftraggeber und Bank.
     */
    public function getInstructionId(): ?string {
        return $this->instructionId;
    }

    /**
     * Returns the UETR (Unique End-to-End Transaction Reference).
     * UUID v4 format for gpi tracking.
     */
    public function getUetr(): ?string {
        return $this->uetr;
    }

    /**
     * Generiert eine neue UETR (UUID v4).
     */
    public static function generateUetr(): string {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variant

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Creates a PaymentIdentification with only EndToEndId.
     */
    public static function fromEndToEndId(string $endToEndId): self {
        return new self(endToEndId: $endToEndId);
    }

    /**
     * Creates a PaymentIdentification with EndToEndId and InstructionId.
     */
    public static function create(string $endToEndId, ?string $instructionId = null): self {
        return new self(
            endToEndId: $endToEndId,
            instructionId: $instructionId
        );
    }

    /**
     * Creates a PaymentIdentification with generated UETR.
     */
    public static function withUetr(string $endToEndId, ?string $instructionId = null): self {
        return new self(
            endToEndId: $endToEndId,
            instructionId: $instructionId,
            uetr: self::generateUetr()
        );
    }
}
