<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PartyIdentificationTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Entities\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\GenericIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\PartyIdentification;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\OrganisationIdentification;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\PersonIdentification;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PartyIdentificationTest extends TestCase {
    #[Test]
    public function constructorWithMinimalParameters(): void {
        $party = new PartyIdentification();

        $this->assertNull($party->getName());
        $this->assertNull($party->getPostalAddressCountry());
        $this->assertNull($party->getPostalAddressLine());
        $this->assertNull($party->getBicOrBei());
        $this->assertNull($party->getOrganisationId());
        $this->assertNull($party->getBirthDate());
        $this->assertNull($party->getBirthPlace());
        $this->assertNull($party->getBirthCountry());
        $this->assertNull($party->getPersonId());
    }

    #[Test]
    public function constructorWithNameOnly(): void {
        $party = new PartyIdentification(name: 'Max Mustermann');

        $this->assertSame('Max Mustermann', $party->getName());
    }

    #[Test]
    public function constructorWithOrganisationData(): void {
        $orgId = new GenericIdentification('DE123456789', schemeCode: 'TXID');
        $party = new PartyIdentification(
            name: 'Muster GmbH',
            postalAddressCountry: 'DE',
            postalAddressLine: 'Musterstraße 1, 12345 Berlin',
            bicOrBei: 'DEUTDEFF',
            organisationId: $orgId
        );

        $this->assertSame('Muster GmbH', $party->getName());
        $this->assertSame('DE', $party->getPostalAddressCountry());
        $this->assertSame('Musterstraße 1, 12345 Berlin', $party->getPostalAddressLine());
        $this->assertSame('DEUTDEFF', $party->getBicOrBei());
        $this->assertSame($orgId, $party->getOrganisationId());
    }

    #[Test]
    public function constructorWithPersonData(): void {
        $personId = new GenericIdentification('AB123456C', schemeCode: 'NIDN');
        $birthDate = new DateTimeImmutable('1985-03-15');

        $party = new PartyIdentification(
            name: 'Max Mustermann',
            birthDate: $birthDate,
            birthPlace: 'Berlin',
            birthCountry: 'DE',
            personId: $personId
        );

        $this->assertSame('Max Mustermann', $party->getName());
        $this->assertSame($birthDate, $party->getBirthDate());
        $this->assertSame('Berlin', $party->getBirthPlace());
        $this->assertSame('DE', $party->getBirthCountry());
        $this->assertSame($personId, $party->getPersonId());
    }

    #[Test]
    public function isOrganisationWithBic(): void {
        $party = new PartyIdentification(name: 'Bank AG', bicOrBei: 'DEUTDEFF');

        $this->assertTrue($party->isOrganisation());
        $this->assertFalse($party->isPerson());
    }

    #[Test]
    public function isOrganisationWithOrgId(): void {
        $orgId = new GenericIdentification('DE123', schemeCode: 'TXID');
        $party = new PartyIdentification(name: 'Firma', organisationId: $orgId);

        $this->assertTrue($party->isOrganisation());
        $this->assertFalse($party->isPerson());
    }

    #[Test]
    public function isPersonWithBirthDate(): void {
        $party = new PartyIdentification(
            name: 'Max',
            birthDate: new DateTimeImmutable('1990-01-01')
        );

        $this->assertTrue($party->isPerson());
        $this->assertFalse($party->isOrganisation());
    }

    #[Test]
    public function isPersonWithPersonId(): void {
        $personId = new GenericIdentification('123456', schemeCode: 'NIDN');
        $party = new PartyIdentification(name: 'Max', personId: $personId);

        $this->assertTrue($party->isPerson());
        $this->assertFalse($party->isOrganisation());
    }

    #[Test]
    public function getOrganisationIdScheme(): void {
        $orgId = new GenericIdentification('DE123', schemeCode: 'TXID');
        $party = new PartyIdentification(organisationId: $orgId);

        $scheme = $party->getOrganisationIdScheme();

        $this->assertInstanceOf(OrganisationIdentification::class, $scheme);
        $this->assertSame(OrganisationIdentification::TXID, $scheme);
    }

    #[Test]
    public function getPersonIdScheme(): void {
        $personId = new GenericIdentification('AB123', schemeCode: 'NIDN');
        $party = new PartyIdentification(personId: $personId);

        $scheme = $party->getPersonIdScheme();

        $this->assertInstanceOf(PersonIdentification::class, $scheme);
        $this->assertSame(PersonIdentification::NIDN, $scheme);
    }

    #[Test]
    public function getAnyIdReturnsBicFirst(): void {
        $orgId = new GenericIdentification('ORG123');
        $party = new PartyIdentification(
            bicOrBei: 'DEUTDEFF',
            organisationId: $orgId
        );

        $this->assertSame('DEUTDEFF', $party->getAnyId());
    }

    #[Test]
    public function getAnyIdReturnsOrgIdWhenNoBic(): void {
        $orgId = new GenericIdentification('ORG123');
        $party = new PartyIdentification(organisationId: $orgId);

        $this->assertSame('ORG123', $party->getAnyId());
    }

    #[Test]
    public function getAnyIdReturnsPersonIdWhenNoOrgId(): void {
        $personId = new GenericIdentification('PERSON123');
        $party = new PartyIdentification(personId: $personId);

        $this->assertSame('PERSON123', $party->getAnyId());
    }

    #[Test]
    public function getAnyIdReturnsNullWhenEmpty(): void {
        $party = new PartyIdentification(name: 'Test');

        $this->assertNull($party->getAnyId());
    }

    #[Test]
    public function toStringWithNameAndId(): void {
        $party = new PartyIdentification(
            name: 'Max Mustermann',
            bicOrBei: 'DEUTDEFF'
        );

        $string = (string) $party;

        $this->assertSame('Max Mustermann (DEUTDEFF)', $string);
    }

    #[Test]
    public function toStringWithNameOnly(): void {
        $party = new PartyIdentification(name: 'Max Mustermann');

        $string = (string) $party;

        $this->assertSame('Max Mustermann', $string);
    }

    #[Test]
    public function toStringEmpty(): void {
        $party = new PartyIdentification();

        $string = (string) $party;

        $this->assertSame('', $string);
    }

    #[Test]
    public function toStringWithIdOnly(): void {
        $party = new PartyIdentification(bicOrBei: 'DEUTDEFF');

        $string = (string) $party;

        $this->assertSame('(DEUTDEFF)', $string);
    }
}
