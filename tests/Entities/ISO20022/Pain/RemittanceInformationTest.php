<?php
/*
 * Created on   : Sun Jul 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : RemittanceInformationTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\RemittanceInformation;
use Tests\Contracts\BaseTestCase;

/**
 * Tests für die Pain RemittanceInformation Entity.
 */
class RemittanceInformationTest extends BaseTestCase {
    public function testConstructorWithUnstructured(): void {
        $remittance = new RemittanceInformation(
            unstructured: ['Rechnung Nr. 12345', 'Kunde: Mustermann']
        );

        $this->assertSame(['Rechnung Nr. 12345', 'Kunde: Mustermann'], $remittance->getUnstructured());
        $this->assertNull($remittance->getCreditorReference());
        $this->assertNull($remittance->getCreditorReferenceType());
        $this->assertFalse($remittance->hasStructured());
    }

    public function testConstructorWithStructured(): void {
        $remittance = new RemittanceInformation(
            unstructured: [],
            creditorReference: 'RF18539007547034',
            creditorReferenceType: 'SCOR'
        );

        $this->assertEmpty($remittance->getUnstructured());
        $this->assertSame('RF18539007547034', $remittance->getCreditorReference());
        $this->assertSame('SCOR', $remittance->getCreditorReferenceType());
        $this->assertTrue($remittance->hasStructured());
    }

    public function testGetUnstructuredString(): void {
        $remittance = new RemittanceInformation(
            unstructured: ['Rechnung Nr. 12345', 'Kunde: Mustermann']
        );

        $this->assertSame('Rechnung Nr. 12345 Kunde: Mustermann', $remittance->getUnstructuredString());
    }

    public function testFromText(): void {
        $text = 'Rechnung Nr. 12345 für Lieferung Januar 2025';
        $remittance = RemittanceInformation::fromText($text);

        $unstructured = $remittance->getUnstructured();
        $this->assertNotEmpty($unstructured);
        $this->assertSame($text, $remittance->getUnstructuredString());
    }

    public function testFromTextSplitsLongText(): void {
        // Generiere einen Text mit mehr als 140 Zeichen
        $text = str_repeat('A', 200);
        $remittance = RemittanceInformation::fromText($text);

        $unstructured = $remittance->getUnstructured();
        $this->assertCount(2, $unstructured);
        $this->assertSame(140, strlen($unstructured[0]));
        $this->assertSame(60, strlen($unstructured[1]));
    }

    public function testFromCreditorReference(): void {
        $remittance = RemittanceInformation::fromCreditorReference('RF18539007547034');

        $this->assertEmpty($remittance->getUnstructured());
        $this->assertSame('RF18539007547034', $remittance->getCreditorReference());
        $this->assertSame('SCOR', $remittance->getCreditorReferenceType());
        $this->assertTrue($remittance->hasStructured());
    }

    public function testCreateWithTextOnly(): void {
        $remittance = RemittanceInformation::create('Miete Januar 2025');

        $this->assertNotEmpty($remittance->getUnstructured());
        $this->assertNull($remittance->getCreditorReference());
        $this->assertNull($remittance->getCreditorReferenceType());
        $this->assertFalse($remittance->hasStructured());
    }

    public function testCreateWithTextAndReference(): void {
        $remittance = RemittanceInformation::create(
            text: 'Miete Januar 2025',
            creditorReference: 'RF18539007547034'
        );

        $this->assertNotEmpty($remittance->getUnstructured());
        $this->assertSame('RF18539007547034', $remittance->getCreditorReference());
        $this->assertSame('SCOR', $remittance->getCreditorReferenceType());
        $this->assertTrue($remittance->hasStructured());
    }

    public function testReadonlyClass(): void {
        $remittance = new RemittanceInformation(unstructured: ['Test']);

        $reflection = new \ReflectionClass($remittance);
        $this->assertTrue($reflection->isReadOnly());
    }
}
