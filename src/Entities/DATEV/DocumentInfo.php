<?php
/*
 * Created on   : Mon Dec 15 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DocumentInfo.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\DATEV;

use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\Category;

/**
 * Information about a DATEV format.
 * Kapselt alle relevanten Details eines erkannten DATEV-Formats.
 */
final readonly class DocumentInfo {
    public function __construct(
        private Category $category,
        private int $version,
        private ?string $definitionClass = null
    ) {
    }

    /**
     * Returns the Category enum.
     */
    public function getCategory(): Category {
        return $this->category;
    }

    /**
     * Returns the format type as string.
     */
    public function getType(): string {
        return $this->category->nameValue();
    }

    /**
     * Returns the category number.
     */
    public function getCategoryNumber(): int {
        return $this->category->value;
    }

    /**
     * Returns the format version.
     */
    public function getVersion(): int {
        return $this->version;
    }

    /**
     * Returns the definition class (if implemented).
     */
    public function getDefinitionClass(): ?string {
        return $this->definitionClass;
    }

    /**
     * Checks if the format is supported.
     */
    public function isSupported(): bool {
        return $this->definitionClass !== null;
    }

    /**
     * Returns a string representation.
     */
    public function __toString(): string {
        $supported = $this->isSupported() ? 'unterstützt' : 'nicht unterstützt';
        return "{$this->getType()} v{$this->version} ({$supported})";
    }
}
