<?php
/*
 * Created on   : Mon Dec 15 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : MetaHeaderDefinitionAbstract.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\DATEV\{MetaHeaderFieldInterface, MetaHeaderDefinitionInterface};
use InvalidArgumentException;

/**
 * Abstract base class for DATEV MetaHeader definitions.
 * Implements common logic for all DATEV versions.
 */
abstract class MetaHeaderDefinitionAbstract implements MetaHeaderDefinitionInterface {
    /**
     * DATEV version number - must be defined as constant in child classes.
     */
    protected const VERSION = null;

    public function getVersion(): int {
        $version = static::VERSION;
        if ($version === null) {
            throw new InvalidArgumentException('VERSION constant must be defined in ' . static::class . ' be defined');
        }
        return $version;
    }

    public function countFields(): int {
        return count($this->getFields());
    }

    /**
     * Regex pattern for a field from the field definition.
     * Default implementation delegates to the field itself.
     */
    public function getValidationPattern(MetaHeaderFieldInterface $field): ?string {
        return $field->pattern();
    }

    /**
     * Abstract methods - must be implemented in child classes.
     */

    /** @return class-string<MetaHeaderFieldInterface> */
    abstract public function getFieldEnum(): string;

    /** @return list<MetaHeaderFieldInterface> */
    abstract public function getFields(): array;

    abstract public function getDefaultValue(MetaHeaderFieldInterface $field): mixed;
}
