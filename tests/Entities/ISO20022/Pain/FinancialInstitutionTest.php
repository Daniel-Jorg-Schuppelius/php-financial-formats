<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : FinancialInstitutionTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PostalAddress;
use CommonToolkit\Enums\CountryCode;
use Tests\Contracts\BaseTestCase;

class FinancialInstitutionTest extends BaseTestCase {
    public function testConstructorWithAllFields(): void {
        $postalAddress = new PostalAddress(
            streetName: 'Bankstraße',
            buildingNumber: '1',
            postCode: '60311',
            townName: 'Frankfurt',
            country: CountryCode::Germany
        );

        $institution = new FinancialInstitution(
            bic: 'COBADEFFXXX',
            name: 'Commerzbank AG',
            postalAddress: $postalAddress,
            clearingSystemId: 'DEBLZ',
            memberId: '37040044',
            lei: '851WYGNLUQLFZBSYGB56'
        );

        $this->assertSame('COBADEFFXXX', $institution->getBic());
        $this->assertSame('Commerzbank AG', $institution->getName());
        $this->assertSame($postalAddress, $institution->getPostalAddress());
        $this->assertSame('DEBLZ', $institution->getClearingSystemId());
        $this->assertSame('37040044', $institution->getMemberId());
        $this->assertSame('851WYGNLUQLFZBSYGB56', $institution->getLei());
    }

    public function testFromBic(): void {
        $institution = FinancialInstitution::fromBic('COBADEFFXXX');

        $this->assertSame('COBADEFFXXX', $institution->getBic());
        $this->assertNull($institution->getName());
        $this->assertTrue($institution->isValid());
    }

    public function testFromNameAndBic(): void {
        $institution = FinancialInstitution::fromNameAndBic('Deutsche Bank', 'DEUTDEFFXXX');

        $this->assertSame('Deutsche Bank', $institution->getName());
        $this->assertSame('DEUTDEFFXXX', $institution->getBic());
        $this->assertTrue($institution->isValid());
    }

    public function testIsValidWithBic(): void {
        $institution = new FinancialInstitution(bic: 'COBADEFFXXX');
        $this->assertTrue($institution->isValid());
    }

    public function testIsValidWithName(): void {
        $institution = new FinancialInstitution(name: 'Test Bank');
        $this->assertTrue($institution->isValid());
    }

    public function testIsValidWithNoIdentification(): void {
        $institution = new FinancialInstitution(memberId: '12345');
        $this->assertFalse($institution->isValid());
    }

    public function testEmptyInstitution(): void {
        $institution = new FinancialInstitution();

        $this->assertNull($institution->getBic());
        $this->assertNull($institution->getName());
        $this->assertFalse($institution->isValid());
    }

    public function testReadonlyClass(): void {
        $institution = new FinancialInstitution(bic: 'COBADEFFXXX');
        $reflection = new \ReflectionClass($institution);
        $this->assertTrue($reflection->isReadOnly());
    }
}
