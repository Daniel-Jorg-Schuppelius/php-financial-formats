<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PostalAddressTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PostalAddress;
use CommonToolkit\Enums\CountryCode;
use Tests\Contracts\BaseTestCase;

class PostalAddressTest extends BaseTestCase {
    public function testConstructorWithAllFields(): void {
        $address = new PostalAddress(
            streetName: 'Musterstraße',
            buildingNumber: '123a',
            postCode: '12345',
            townName: 'Berlin',
            country: CountryCode::Germany,
            addressLines: ['Hinterhaus', 'Etage 3'],
            department: 'Buchhaltung',
            subDepartment: 'Kreditorenbuchhaltung'
        );

        $this->assertSame('Musterstraße', $address->getStreetName());
        $this->assertSame('123a', $address->getBuildingNumber());
        $this->assertSame('12345', $address->getPostCode());
        $this->assertSame('Berlin', $address->getTownName());
        $this->assertSame(CountryCode::Germany, $address->getCountry());
        $this->assertSame(['Hinterhaus', 'Etage 3'], $address->getAddressLines());
        $this->assertSame('Buchhaltung', $address->getDepartment());
        $this->assertSame('Kreditorenbuchhaltung', $address->getSubDepartment());
    }

    public function testConstructorWithMinimalFields(): void {
        $address = new PostalAddress();

        $this->assertNull($address->getStreetName());
        $this->assertNull($address->getBuildingNumber());
        $this->assertNull($address->getPostCode());
        $this->assertNull($address->getTownName());
        $this->assertNull($address->getCountry());
        $this->assertEmpty($address->getAddressLines());
        $this->assertNull($address->getDepartment());
        $this->assertNull($address->getSubDepartment());
    }

    public function testFormatWithStreetAndCity(): void {
        $address = new PostalAddress(
            streetName: 'Hauptstraße',
            buildingNumber: '42',
            postCode: '10115',
            townName: 'Berlin',
            country: CountryCode::Germany
        );

        $formatted = $address->format();

        $this->assertStringContainsString('Hauptstraße 42', $formatted);
        $this->assertStringContainsString('10115 Berlin', $formatted);
        $this->assertStringContainsString('DE', $formatted);
    }

    public function testFormatWithDepartment(): void {
        $address = new PostalAddress(
            streetName: 'Bankstraße',
            buildingNumber: '1',
            postCode: '60311',
            townName: 'Frankfurt',
            department: 'Abteilung Finanzen'
        );

        $formatted = $address->format();

        $this->assertStringContainsString('Abteilung Finanzen', $formatted);
        $this->assertStringContainsString('Bankstraße 1', $formatted);
    }

    public function testFormatWithAddressLines(): void {
        $address = new PostalAddress(
            addressLines: ['c/o Max Mustermann', 'Postfach 12345'],
            postCode: '12345',
            townName: 'Stadt'
        );

        $formatted = $address->format();

        $this->assertStringContainsString('c/o Max Mustermann', $formatted);
        $this->assertStringContainsString('Postfach 12345', $formatted);
        $this->assertStringContainsString('12345 Stadt', $formatted);
    }

    public function testFormatWithOnlyTown(): void {
        $address = new PostalAddress(townName: 'München');

        $formatted = $address->format();

        $this->assertStringContainsString('München', $formatted);
    }

    public function testReadonlyClass(): void {
        $address = new PostalAddress(townName: 'Berlin');
        $reflection = new \ReflectionClass($address);
        $this->assertTrue($reflection->isReadOnly());
    }
}
