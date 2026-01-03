<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Trailer.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Swift;

/**
 * SWIFT FIN Trailer Block (Block 5)
 * 
 * Format: {5:{CHK:123456789ABC}{TNG:}{PDE:1348120811BANKFRPPAXXX2222123456}}
 * 
 * Possible fields:
 * - CHK: Checksum (12 hex Zeichen)
 * - TNG: Training Message Flag
 * - PDE: Possible Duplicate Emission
 * - PDM: Possible Duplicate Message
 * - DLM: Delayed Message
 * - MRF: Message Reference
 * - SYS: System Originated Message
 */
final class Trailer {
    /**
     * @param array<string, string> $fields Die Trailer Felder (Tag => Wert)
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
     * Returns the checksum
     */
    public function getChecksum(): ?string {
        return $this->getField('CHK');
    }

    /**
     * Checks if this is a training message
     */
    public function isTraining(): bool {
        return $this->hasField('TNG');
    }

    /**
     * Checks if this is a possible duplicate emission
     */
    public function isPossibleDuplicateEmission(): bool {
        return $this->hasField('PDE');
    }

    /**
     * Checks if this is a possible duplicate message
     */
    public function isPossibleDuplicateMessage(): bool {
        return $this->hasField('PDM');
    }

    /**
     * Checks if this is a delayed message
     */
    public function isDelayed(): bool {
        return $this->hasField('DLM');
    }

    /**
     * Checks if this is a system-generated message
     */
    public function isSystemOriginated(): bool {
        return $this->hasField('SYS');
    }

    /**
     * Checks if a specific field is present
     */
    public function hasField(string $tag): bool {
        return isset($this->fields[$tag]);
    }

    /**
     * Returns the complete Block 5 string
     */
    public function __toString(): string {
        if (empty($this->fields)) {
            return '';
        }

        $content = '';
        foreach ($this->fields as $tag => $value) {
            $content .= '{' . $tag . ':' . $value . '}';
        }
        return '{5:' . $content . '}';
    }

    /**
     * Parst einen Block 5 String
     * 
     * @param string $raw Roher Block-Inhalt (ohne {5: und })
     */
    public static function parse(string $raw): self {
        $fields = [];

        // Parse nested {tag:value} pairs
        if (preg_match_all('/\{([A-Z]+):([^}]*)\}/', $raw, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $fields[$match[1]] = $match[2];
            }
        }

        return new self($fields);
    }
}
