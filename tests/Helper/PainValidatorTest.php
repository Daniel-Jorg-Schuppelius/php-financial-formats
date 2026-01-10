<?php
/*
 * Created on   : Fri Jan 10 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PainValidatorTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Helper;

use CommonToolkit\Entities\XML\XsdValidationResult;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Pain\PainType;
use CommonToolkit\FinancialFormats\Helper\Data\PainValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Contracts\BaseTestCase;

class PainValidatorTest extends BaseTestCase {
    private string $xsdPath;

    protected function setUp(): void {
        parent::setUp();
        $this->xsdPath = dirname(__DIR__, 2) . '/data/xsd/pain/';
    }

    #[Test]
    public function testGetAvailableSchemas(): void {
        $schemas = PainValidator::getAvailableSchemas();

        $this->assertIsArray($schemas);
        $this->assertArrayHasKey('pain.001', $schemas);
        $this->assertArrayHasKey('pain.002', $schemas);
        $this->assertArrayHasKey('pain.008', $schemas);
    }

    #[Test]
    public function testDetectTypeFromNamespace(): void {
        $xml001 = '<?xml version="1.0"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.12"></Document>';
        $xml002 = '<?xml version="1.0"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.002.001.14"></Document>';
        $xml008 = '<?xml version="1.0"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.11"></Document>';

        $this->assertEquals(PainType::PAIN_001, PainValidator::detectType($xml001));
        $this->assertEquals(PainType::PAIN_002, PainValidator::detectType($xml002));
        $this->assertEquals(PainType::PAIN_008, PainValidator::detectType($xml008));
    }

    #[Test]
    public function testDetectVersionFromNamespace(): void {
        $xml12 = '<?xml version="1.0"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.12"></Document>';
        $xml14 = '<?xml version="1.0"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.002.001.14"></Document>';
        $xml11 = '<?xml version="1.0"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.11"></Document>';

        $this->assertEquals('12', PainValidator::detectVersion($xml12));
        $this->assertEquals('14', PainValidator::detectVersion($xml14));
        $this->assertEquals('11', PainValidator::detectVersion($xml11));
    }

    #[Test]
    public function testValidateReturnsValidationResult(): void {
        $xml = '<?xml version="1.0"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.12"></Document>';
        $result = PainValidator::validate($xml);

        $this->assertInstanceOf(XsdValidationResult::class, $result);
        $this->assertEquals(PainType::PAIN_001, $result->type);
        $this->assertEquals('12', $result->version);
    }

    #[Test]
    public function testValidateWithUnknownTypeReturnsError(): void {
        $xml = '<?xml version="1.0"?><Document xmlns="urn:example:unknown"></Document>';
        $result = PainValidator::validate($xml);

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('Unknown document type', $result->getFirstError());
    }

    #[Test]
    public function testValidationResultMethods(): void {
        $result = new XsdValidationResult(
            valid: false,
            errors: ['Error 1', 'Error 2'],
            type: PainType::PAIN_001,
            version: '12',
            xsdFile: '/path/to/schema.xsd'
        );

        $this->assertFalse($result->isValid());
        $this->assertEquals(2, $result->countErrors());
        $this->assertEquals('Error 1', $result->getFirstError());
        $this->assertStringContainsString('Error 1', $result->getErrorsAsString());
        $this->assertEquals('/path/to/schema.xsd', $result->xsdFile);
    }

    #[Test]
    public function testValidationResultWithNoErrors(): void {
        $result = new XsdValidationResult(
            valid: true,
            errors: [],
            type: PainType::PAIN_001,
            version: '12'
        );

        $this->assertTrue($result->isValid());
        $this->assertEquals(0, $result->countErrors());
        $this->assertNull($result->getFirstError());
        $this->assertEquals('', $result->getErrorsAsString());
    }

    public static function xsdFilesProvider(): array {
        return [
            ['pain.001.001.12.xsd'],
            ['pain.002.001.14.xsd'],
            ['pain.008.001.11.xsd'],
        ];
    }

    #[Test]
    #[DataProvider('xsdFilesProvider')]
    public function testXsdFileExists(string $filename): void {
        $file = $this->xsdPath . $filename;
        $this->assertFileExists($file, "XSD file {$filename} should exist");
    }

    #[Test]
    public function testValidateMalformedXml(): void {
        $malformedXml = '<?xml version="1.0"?><Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.12"><unclosed>';
        $result = PainValidator::validate($malformedXml);

        $this->assertFalse($result->isValid());
        $this->assertNotEmpty($result->getErrors());
    }
}
