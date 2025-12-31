<?php
/*
 * Created on   : Sun Nov 23 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : MetaHeaderDefinitionInterface.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Contracts\Interfaces\DATEV;

interface MetaHeaderDefinitionInterface {
    public function getDefaultValue(MetaHeaderFieldInterface $field): mixed;

    public function countFields(): int;

    /** @return class-string<MetaHeaderFieldInterface> */
    public function getFieldEnum(): string;

    /** @return list<MetaHeaderFieldInterface> */
    public function getFields(): array;

    public function getVersion(): int;
}
