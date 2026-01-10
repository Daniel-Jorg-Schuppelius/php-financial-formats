<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : GenericIdentificationTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Entities\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\GenericIdentification;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\AccountIdentificationType;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\ClearingSystemIdentification;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\OrganisationIdentification;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\PersonIdentification;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GenericIdentificationTest extends TestCase {
    #[Test]
    public function constructorWithIdOnly(): void {
        $id = new GenericIdentification('12345');

        $this->assertSame('12345', $id->getId());
        $this->assertNull($id->getSchemeCode());
        $this->assertNull($id->getSchemeProprietary());
        $this->assertNull($id->getIssuer());
    }

    #[Test]
    public function constructorWithAllParameters(): void {
        $id = new GenericIdentification(
            id: 'ABC123',
            schemeCode: 'CUST',
            schemeProprietary: null,
            issuer: 'BankXYZ'
        );

        $this->assertSame('ABC123', $id->getId());
        $this->assertSame('CUST', $id->getSchemeCode());
        $this->assertNull($id->getSchemeProprietary());
        $this->assertSame('BankXYZ', $id->getIssuer());
    }

    #[Test]
    public function constructorWithProprietaryScheme(): void {
        $id = new GenericIdentification(
            id: 'PROP123',
            schemeCode: null,
            schemeProprietary: 'MyCustomScheme'
        );

        $this->assertSame('PROP123', $id->getId());
        $this->assertNull($id->getSchemeCode());
        $this->assertSame('MyCustomScheme', $id->getSchemeProprietary());
    }

    #[Test]
    public function hasCodelistScheme(): void {
        $withCode = new GenericIdentification('123', schemeCode: 'CUST');
        $withoutCode = new GenericIdentification('123');

        $this->assertTrue($withCode->hasCodelistScheme());
        $this->assertFalse($withoutCode->hasCodelistScheme());
    }

    #[Test]
    public function hasProprietaryScheme(): void {
        $withProprietary = new GenericIdentification('123', schemeProprietary: 'Custom');
        $withoutProprietary = new GenericIdentification('123');

        $this->assertTrue($withProprietary->hasProprietaryScheme());
        $this->assertFalse($withoutProprietary->hasProprietaryScheme());
    }

    #[Test]
    public function getAccountIdentificationType(): void {
        // Valid account identification type
        $id = new GenericIdentification('12345678', schemeCode: 'BBAN');
        $type = $id->getAccountIdentificationType();

        $this->assertInstanceOf(AccountIdentificationType::class, $type);
        $this->assertSame(AccountIdentificationType::BBAN, $type);
    }

    #[Test]
    public function getAccountIdentificationTypeReturnsNullForInvalid(): void {
        $id = new GenericIdentification('123', schemeCode: 'INVALID_CODE');

        $this->assertNull($id->getAccountIdentificationType());
    }

    #[Test]
    public function getClearingSystemIdentification(): void {
        $id = new GenericIdentification('DEUTDEFF', schemeCode: 'DEBLZ');
        $type = $id->getClearingSystemIdentification();

        $this->assertInstanceOf(ClearingSystemIdentification::class, $type);
        $this->assertSame(ClearingSystemIdentification::DEBLZ, $type);
    }

    #[Test]
    public function getOrganisationIdentification(): void {
        $id = new GenericIdentification('DE123456789', schemeCode: 'TXID');
        $type = $id->getOrganisationIdentification();

        $this->assertInstanceOf(OrganisationIdentification::class, $type);
        $this->assertSame(OrganisationIdentification::TXID, $type);
    }

    #[Test]
    public function getPersonIdentification(): void {
        $id = new GenericIdentification('AB123456C', schemeCode: 'NIDN');
        $type = $id->getPersonIdentification();

        $this->assertInstanceOf(PersonIdentification::class, $type);
        $this->assertSame(PersonIdentification::NIDN, $type);
    }

    #[Test]
    public function toStringWithScheme(): void {
        $id = new GenericIdentification('12345', schemeCode: 'CUST');
        $string = (string) $id;

        $this->assertStringContainsString('12345', $string);
    }

    #[Test]
    public function toStringWithoutScheme(): void {
        $id = new GenericIdentification('12345');
        $string = (string) $id;

        $this->assertSame('12345', $string);
    }

    #[Test]
    public function enumConversionReturnsNullWithoutSchemeCode(): void {
        $id = new GenericIdentification('12345');

        $this->assertNull($id->getAccountIdentificationType());
        $this->assertNull($id->getClearingSystemIdentification());
        $this->assertNull($id->getOrganisationIdentification());
        $this->assertNull($id->getPersonIdentification());
    }
}
