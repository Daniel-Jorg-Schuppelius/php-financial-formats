<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : RemittanceInformation.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain;

/**
 * Remittance information for pain messages.
 * 
 * Verwendungszweck einer Zahlung. Kann entweder unstrukturiert (Freitext)
 * oder strukturiert (Referenznummern, Rechnungsdaten) sein.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain
 */
final readonly class RemittanceInformation {
    /**
     * @param string[] $unstructured Unstrukturierte Verwendungszweckzeilen
     */
    public function __construct(
        private array $unstructured = [],
        private ?string $creditorReference = null,
        private ?string $creditorReferenceType = null
    ) {
    }

    /**
     * Returns the unstructured remittance lines (Ustrd).
     * @return string[]
     */
    public function getUnstructured(): array {
        return $this->unstructured;
    }

    /**
     * Returns the remittance information as a single string.
     */
    public function getUnstructuredString(): string {
        return implode(' ', $this->unstructured);
    }

    /**
     * Returns the structured creditor reference (CdtrRef/Ref).
     * Z.B. ISO 11649 Referenz (RF-Referenz).
     */
    public function getCreditorReference(): ?string {
        return $this->creditorReference;
    }

    /**
     * Returns the creditor reference type (CdtrRef/Tp).
     * E.g. "SCOR" for ISO 11649.
     */
    public function getCreditorReferenceType(): ?string {
        return $this->creditorReferenceType;
    }

    /**
     * Checks if structured information is present.
     */
    public function hasStructured(): bool {
        return $this->creditorReference !== null;
    }

    /**
     * Erstellt aus unstrukturiertem Text.
     */
    public static function fromText(string $text): self {
        // Splitten in max. 140 Zeichen pro Zeile (pain.001 Limit)
        $lines = str_split($text, 140);
        return new self(unstructured: $lines);
    }

    /**
     * Creates with ISO 11649 creditor reference (RF reference).
     */
    public static function fromCreditorReference(string $reference): self {
        return new self(
            creditorReference: $reference,
            creditorReferenceType: 'SCOR'
        );
    }

    /**
     * Erstellt mit Text und Referenz kombiniert.
     */
    public static function create(string $text, ?string $creditorReference = null): self {
        $lines = str_split($text, 140);
        return new self(
            unstructured: $lines,
            creditorReference: $creditorReference,
            creditorReferenceType: $creditorReference !== null ? 'SCOR' : null
        );
    }
}
