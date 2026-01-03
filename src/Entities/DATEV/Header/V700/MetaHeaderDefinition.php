<?php
/*
 * Created on   : Mon Dec 01 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : MetaHeaderDefinition.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\DATEV\Header\V700;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\DATEV\MetaHeaderDefinitionAbstract;
use CommonToolkit\FinancialFormats\Contracts\Interfaces\DATEV\MetaHeaderFieldInterface;
use CommonToolkit\Enums\CurrencyCode;
use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\{AccountingPurpose, BookingType, Establishing, Mark};
use CommonToolkit\FinancialFormats\Enums\DATEV\MetaFields\Format\{Category, Version};
use CommonToolkit\FinancialFormats\Enums\DATEV\HeaderFields\V700\MetaHeaderField;
use InvalidArgumentException;

final class MetaHeaderDefinition extends MetaHeaderDefinitionAbstract {
    protected const VERSION = 700;

    /**
     * Liefert den Enum-Typ, der die Meta-Header fields beschreibt.
     *
     * @return class-string<MetaHeaderField>
     */
    public function getFieldEnum(): string {
        return MetaHeaderField::class;
    }

    /**
     * Reihenfolge der Meta-Header fields wie von DATEV spezifiziert.
     *
     * @return MetaHeaderFieldInterface[]
     */
    public function getFields(): array {
        // MetaHeaderField::ordered() muss die Positionen 1–31 exakt abbilden.
        return MetaHeaderField::ordered();
    }

    /**
     * Returns the business default values for the meta header.
     *
     * Enums werden hier auf ihre skalaren Werte (int/string) abgebildet, damit
     * CSV-Ausgabe und Regex-Validierung konsistent bleiben.
     */
    public function getDefaultValue(MetaHeaderFieldInterface $field): mixed {
        if (!$field instanceof MetaHeaderField) {
            throw new InvalidArgumentException('Inkompatibles Feldobjekt übergeben.');
        }

        // Default-Format für diese Definition (V700 BookingBatch)
        $category = Category::Buchungsstapel;

        return match ($field) {
            MetaHeaderField::Kennzeichen           => Mark::EXTF->value,
            MetaHeaderField::Versionsnummer        => $this->getVersion(),
            MetaHeaderField::Formatkategorie       => $category->value,
            MetaHeaderField::Formatname            => $category->nameValue(),
            MetaHeaderField::Formatversion         => Version::forCategory($category)->value,
            MetaHeaderField::Buchungstyp           => BookingType::FinancialAccounting->value,
            MetaHeaderField::Rechnungslegungszweck => AccountingPurpose::INDEPENDENT->value,
            MetaHeaderField::Festschreibung        => Establishing::NONE->value,
            MetaHeaderField::Waehrungskennzeichen  => CurrencyCode::Euro->value,
            default => null,
        };
    }
}
