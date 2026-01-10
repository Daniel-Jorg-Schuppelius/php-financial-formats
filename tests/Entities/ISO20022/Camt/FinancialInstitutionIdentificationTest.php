<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : FinancialInstitutionIdentificationTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Entities\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\FinancialInstitutionIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\GenericIdentification;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\ClearingSystemIdentification;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FinancialInstitutionIdentificationTest extends TestCase {
    #[Test]
    public function constructorWithMinimalParameters(): void {
        $fi = new FinancialInstitutionIdentification();

        $this->assertNull($fi->getBic());
        $this->assertNull($fi->getClearingSystemCode());
        $this->assertNull($fi->getClearingSystemProprietary());
        $this->assertNull($fi->getClearingMemberId());
        $this->assertNull($fi->getName());
        $this->assertNull($fi->getOtherId());
    }

    #[Test]
    public function constructorWithBic(): void {
        $fi = new FinancialInstitutionIdentification(bic: 'DEUTDEFF');

        $this->assertSame('DEUTDEFF', $fi->getBic());
        $this->assertTrue($fi->hasBic());
    }

    #[Test]
    public function constructorWithAllParameters(): void {
        $otherId = new GenericIdentification('OTHER123', schemeCode: 'CUST');

        $fi = new FinancialInstitutionIdentification(
            bic: 'DEUTDEFF',
            clearingSystemCode: 'DEBLZ',
            clearingSystemProprietary: null,
            clearingMemberId: '10070000',
            name: 'Deutsche Bank AG',
            otherId: $otherId
        );

        $this->assertSame('DEUTDEFF', $fi->getBic());
        $this->assertSame('DEBLZ', $fi->getClearingSystemCode());
        $this->assertNull($fi->getClearingSystemProprietary());
        $this->assertSame('10070000', $fi->getClearingMemberId());
        $this->assertSame('Deutsche Bank AG', $fi->getName());
        $this->assertSame($otherId, $fi->getOtherId());
    }

    #[Test]
    public function constructorWithClearingSystemProprietary(): void {
        $fi = new FinancialInstitutionIdentification(
            clearingSystemProprietary: 'CustomClearing',
            clearingMemberId: '12345'
        );

        $this->assertNull($fi->getClearingSystemCode());
        $this->assertSame('CustomClearing', $fi->getClearingSystemProprietary());
        $this->assertSame('12345', $fi->getClearingMemberId());
    }

    #[Test]
    public function hasBic(): void {
        $withBic = new FinancialInstitutionIdentification(bic: 'DEUTDEFF');
        $withoutBic = new FinancialInstitutionIdentification();

        $this->assertTrue($withBic->hasBic());
        $this->assertFalse($withoutBic->hasBic());
    }

    #[Test]
    public function hasClearingSystemWithCode(): void {
        $fi = new FinancialInstitutionIdentification(clearingSystemCode: 'DEBLZ');

        $this->assertTrue($fi->hasClearingSystem());
    }

    #[Test]
    public function hasClearingSystemWithProprietary(): void {
        $fi = new FinancialInstitutionIdentification(clearingSystemProprietary: 'Custom');

        $this->assertTrue($fi->hasClearingSystem());
    }

    #[Test]
    public function hasClearingSystemFalse(): void {
        $fi = new FinancialInstitutionIdentification(bic: 'DEUTDEFF');

        $this->assertFalse($fi->hasClearingSystem());
    }

    #[Test]
    public function getClearingSystemIdentification(): void {
        $fi = new FinancialInstitutionIdentification(clearingSystemCode: 'DEBLZ');
        $enum = $fi->getClearingSystemIdentification();

        $this->assertInstanceOf(ClearingSystemIdentification::class, $enum);
        $this->assertSame(ClearingSystemIdentification::DEBLZ, $enum);
    }

    #[Test]
    public function getClearingSystemIdentificationReturnsNull(): void {
        $fi = new FinancialInstitutionIdentification();

        $this->assertNull($fi->getClearingSystemIdentification());
    }

    #[Test]
    public function getClearingSystemIdentificationInvalidCode(): void {
        $fi = new FinancialInstitutionIdentification(clearingSystemCode: 'INVALID');

        $this->assertNull($fi->getClearingSystemIdentification());
    }

    #[Test]
    public function getPrimaryIdReturnsBicFirst(): void {
        $otherId = new GenericIdentification('OTHER123');
        $fi = new FinancialInstitutionIdentification(
            bic: 'DEUTDEFF',
            clearingMemberId: '12345',
            otherId: $otherId
        );

        $this->assertSame('DEUTDEFF', $fi->getPrimaryId());
    }

    #[Test]
    public function getPrimaryIdReturnsClearingMemberWhenNoBic(): void {
        $otherId = new GenericIdentification('OTHER123');
        $fi = new FinancialInstitutionIdentification(
            clearingMemberId: '12345',
            otherId: $otherId
        );

        $this->assertSame('12345', $fi->getPrimaryId());
    }

    #[Test]
    public function getPrimaryIdReturnsOtherIdWhenNoBicOrClearing(): void {
        $otherId = new GenericIdentification('OTHER123');
        $fi = new FinancialInstitutionIdentification(otherId: $otherId);

        $this->assertSame('OTHER123', $fi->getPrimaryId());
    }

    #[Test]
    public function getPrimaryIdReturnsNullWhenEmpty(): void {
        $fi = new FinancialInstitutionIdentification(name: 'Test Bank');

        $this->assertNull($fi->getPrimaryId());
    }

    #[Test]
    public function toStringWithNameAndBic(): void {
        $fi = new FinancialInstitutionIdentification(
            bic: 'DEUTDEFF',
            name: 'Deutsche Bank AG'
        );

        $string = (string) $fi;

        $this->assertSame('Deutsche Bank AG (DEUTDEFF)', $string);
    }

    #[Test]
    public function toStringWithNameOnly(): void {
        $fi = new FinancialInstitutionIdentification(name: 'Test Bank');

        $string = (string) $fi;

        $this->assertSame('Test Bank', $string);
    }

    #[Test]
    public function toStringWithBicOnly(): void {
        $fi = new FinancialInstitutionIdentification(bic: 'DEUTDEFF');

        $string = (string) $fi;

        $this->assertSame('(DEUTDEFF)', $string);
    }

    #[Test]
    public function toStringEmpty(): void {
        $fi = new FinancialInstitutionIdentification();

        $string = (string) $fi;

        $this->assertSame('', $string);
    }
}
