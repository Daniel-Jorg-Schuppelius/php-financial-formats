<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : CamtValidator.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Helper\Data;

use CommonToolkit\Entities\XML\XsdValidationResult;
use CommonToolkit\FinancialFormats\Contracts\Abstracts\ISO20022\ValidatorAbstract;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtVersion;
use UnitEnum;

/**
 * CAMT validator for XSD schema validation.
 * 
 * Validates all CAMT XML documents (camt.003 - camt.109)
 * against the corresponding ISO 20022 XSD schemas.
 * 
 * XSD files are dynamically discovered from the filesystem.
 * 
 * @package CommonToolkit\FinancialFormats\Helper\Data
 */
class CamtValidator extends ValidatorAbstract {
    /**
     * Path to the XSD directory
     */
    private const XSD_BASE_PATH = __DIR__ . '/../../../data/xsd/camt/';

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
        return 'camt';
    }

    /**
     * {@inheritDoc}
     */
    public static function detectType(string $xmlContent): ?CamtType {
        return CamtType::fromXml($xmlContent);
    }

    /**
     * {@inheritDoc}
     */
    protected static function tryFromTypeKey(string $typeKey): ?UnitEnum {
        return CamtType::tryFrom($typeKey);
    }

    /**
     * {@inheritDoc}
     */
    protected static function versionToString(mixed $version): ?string {
        if ($version instanceof CamtVersion) {
            return $version->value;
        }
        return is_string($version) ? $version : null;
    }

    /**
     * Returns all available XSD files including Austrian schemas.
     * 
     * @return array<string, array<string, string>> Type => Version => Filename
     */
    public static function getAvailableSchemas(): array {
        $available = parent::getAvailableSchemas();

        // Add Austrian schemas
        $basePath = self::XSD_BASE_PATH;
        $austrianFiles = glob($basePath . 'ISO.camt.*.xsd');

        if ($austrianFiles !== false) {
            foreach ($austrianFiles as $file) {
                $filename = basename($file);
                // Pattern: ISO.camt.053.001.02.austrian.003.xsd
                if (preg_match('/^ISO\.camt\.(\d{3})\.001\.(\d{2})\.austrian\.(\d{3})/', $filename, $matches)) {
                    $typeKey = 'camt.' . $matches[1] . '.austrian';
                    $version = $matches[2] . '.' . $matches[3];
                    $available[$typeKey][$version] = $filename;
                }
            }
        }

        return $available;
    }
}

/**
 * Result of a CAMT validation.
 * 
 * @deprecated Use XsdValidationResult instead
 */
final class ValidationResult extends XsdValidationResult {
    /**
     * @param bool $valid Whether validation was successful
     * @param array<string> $errors List of errors (empty on success)
     * @param CamtType|null $type The detected CAMT type
     * @param CamtVersion|null $version The detected CAMT version
     * @param string|null $xsdFile The XSD file used
     */
    public function __construct(
        bool $valid,
        array $errors,
        ?CamtType $type,
        ?CamtVersion $version,
        ?string $xsdFile = null
    ) {
        parent::__construct(
            valid: $valid,
            errors: $errors,
            type: $type,
            version: $version?->value,
            xsdFile: $xsdFile
        );
    }

    /**
     * Returns the CAMT type.
     */
    public function getCamtType(): ?CamtType {
        $type = $this->getType();
        return $type instanceof CamtType ? $type : null;
    }

    /**
     * Returns the CAMT version.
     */
    public function getCamtVersion(): ?CamtVersion {
        $version = $this->getVersion();
        return $version !== null ? CamtVersion::tryFrom($version) : null;
    }
}