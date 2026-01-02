<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PartyIdentificationTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PostalAddress;
use CommonToolkit\Enums\CountryCode;
use Tests\Contracts\BaseTestCase;

class PartyIdentificationTest extends BaseTestCase {
    public function testConstructorWithAllFields(): void {
        $postalAddress = new PostalAddress(
            streetName: 'Musterstraße',
            buildingNumber: '123',
            postCode: '12345',
            townName: 'Berlin',
            country: CountryCode::Germany
        );

        $party = new PartyIdentification(
            name: 'Max Mustermann GmbH',
            postalAddress: $postalAddress,
            organisationId: 'DE123456789',
            privateId: null,
            bic: 'COBADEFFXXX',
            lei: '529900T8BM49AURSDO55',
            countryOfResidence: CountryCode::Germany
        );

        $this->assertSame('Max Mustermann GmbH', $party->getName());
        $this->assertSame($postalAddress, $party->getPostalAddress());
        $this->assertSame('DE123456789', $party->getOrganisationId());
        $this->assertNull($party->getPrivateId());
        $this->assertSame('COBADEFFXXX', $party->getBic());
        $this->assertSame('529900T8BM49AURSDO55', $party->getLei());
        $this->assertSame(CountryCode::Germany, $party->getCountryOfResidence());
    }

    public function testConstructorWithMinimalFields(): void {
        $party = new PartyIdentification();

        $this->assertNull($party->getName());
        $this->assertNull($party->getPostalAddress());
        $this->assertNull($party->getOrganisationId());
        $this->assertNull($party->getPrivateId());
        $this->assertNull($party->getBic());
        $this->assertNull($party->getLei());
        $this->assertNull($party->getCountryOfResidence());
    }

    public function testFromName(): void {
        $party = PartyIdentification::fromName('Test Company');

        $this->assertSame('Test Company', $party->getName());
        $this->assertNull($party->getOrganisationId());
        $this->assertTrue($party->isValid());
    }

    public function testIsValidWithName(): void {
        $party = new PartyIdentification(name: 'Test');
        $this->assertTrue($party->isValid());
    }

    public function testIsValidWithOrganisationId(): void {
        $party = new PartyIdentification(organisationId: 'ORG123');
        $this->assertTrue($party->isValid());
    }

    public function testIsValidWithPrivateId(): void {
        $party = new PartyIdentification(privateId: 'PRIV123');
        $this->assertTrue($party->isValid());
    }

    public function testIsValidWithNoIdentification(): void {
        $party = new PartyIdentification(bic: 'COBADEFFXXX');
        $this->assertFalse($party->isValid());
    }

    public function testReadonlyClass(): void {
        $party = new PartyIdentification(name: 'Test');
        $reflection = new \ReflectionClass($party);
        $this->assertTrue($reflection->isReadOnly());
    }
}
