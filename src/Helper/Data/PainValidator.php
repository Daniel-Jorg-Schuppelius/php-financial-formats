<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PainValidator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Helper\Data;

use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\ValidatorAbstract;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\PainType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\PainVersion;
use UnitEnum;

/**
 * Pain validator for XSD schema validation.
 * 
 * Validates all Pain XML documents (pain.001 - pain.018)
 * against the corresponding ISO 20022 XSD schemas.
 * 
 * XSD files are dynamically discovered from the filesystem.
 * 
 * @package CommonToolkit\FinancialFormats\Helper\Data
 */
class PainValidator extends ValidatorAbstract {
    /**
     * Path to the XSD directory
     */
    private const XSD_BASE_PATH = __DIR__ . '/../../../data/xsd/pain/';

    /**
     * {@inheritDoc}
     */
    protected static function getXsdBasePath(): string {
        return self::XSD_BASE_PATH;
    }

    /**
     * {@inheritDoc}
     */
    protected static function getMessageTypePrefix(): string {
        return 'pain';
    }

    /**
     * {@inheritDoc}
     */
    public static function detectType(string $xmlContent): ?PainType {
        return PainType::fromXml($xmlContent);
    }

    /**
     * {@inheritDoc}
     */
    protected static function tryFromTypeKey(string $typeKey): ?UnitEnum {
        return PainType::tryFrom($typeKey);
    }

    /**
     * {@inheritDoc}
     */
    protected static function versionToString(mixed $version): ?string {
        if ($version instanceof PainVersion) {
            return $version->value;
        }
        return is_string($version) ? $version : null;
    }
}