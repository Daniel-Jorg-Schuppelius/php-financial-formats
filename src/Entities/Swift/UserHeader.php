<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : UserHeader.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Swift;

/**
 * SWIFT FIN User Header Block (Block 3)
 * 
 * Format: {3:{108:MUR12345678901234}{119:STP}}
 * 
 * Contains optional user-specific fields:
 * - 103: Service Type Identifier
 * - 108: Message User Reference (MUR)
 * - 111: Service Type Identifier (Request for Transfer)
 * - 113: Banking Priority
 * - 115: Addressee Information
 * - 119: Validation Flag (STP = Straight Through Processing)
 * - 121: Unique End-to-End Transaction Reference (UETR)
 * - 165: Payment Release Information
 * - 433: Sanctions Screening Info
 * - 434: Payment Controls Info
 */
final class UserHeader {
    /**
     * @param array<string, string> $fields Die User Header Felder (Tag => Wert)
     */
    public function __construct(
        private readonly array $fields = []
    ) {
    }

    /**
     * Returns all fields
     * 
     * @return array<string, string>
     */
    public function getFields(): array {
        return $this->fields;
    }

    /**
     * Returns a specific field
     */
    public function getField(string $tag): ?string {
        return $this->fields[$tag] ?? null;
    }

    /**
     * Returns the Service Type Identifier
     */
    public function getServiceTypeId(): ?string {
        return $this->getField('103');
    }

    /**
     * Returns the Message User Reference (MUR)
     */
    public function getMur(): ?string {
        return $this->getField('108');
    }

    /**
     * Returns the Banking Priority
     */
    public function getBankingPriority(): ?string {
        return $this->getField('113');
    }

    /**
     * Returns the Validation Flag (e.g. STP)
     */
    public function getValidationFlag(): ?string {
        return $this->getField('119');
    }

    /**
     * Returns the Unique End-to-End Transaction Reference (UETR)
     */
    public function getUetr(): ?string {
        return $this->getField('121');
    }

    /**
     * Checks if STP (Straight Through Processing) is enabled
     */
    public function isStp(): bool {
        return $this->getField('119') === 'STP';
    }

    /**
     * Checks if a specific field is present
     */
    public function hasField(string $tag): bool {
        return isset($this->fields[$tag]);
    }

    /**
     * Returns the complete Block 3 string
     */
    public function __toString(): string {
        if (empty($this->fields)) {
            return '';
        }

        $content = '';
        foreach ($this->fields as $tag => $value) {
            $content .= '{' . $tag . ':' . $value . '}';
        }
        return '{3:' . $content . '}';
    }

    /**
     * Parst einen Block 3 String
     * 
     * @param string $raw Roher Block-Inhalt (ohne {3: und })
     */
    public static function parse(string $raw): self {
        $fields = [];

        // Parse nested {tag:value} pairs
        if (preg_match_all('/\{(\d+):([^}]*)\}/', $raw, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $fields[$match[1]] = $match[2];
            }
        }

        return new self($fields);
    }
}
