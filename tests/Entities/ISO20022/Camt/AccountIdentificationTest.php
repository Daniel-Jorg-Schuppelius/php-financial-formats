<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : AccountIdentificationTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Entities\ISO20022\Camt;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\AccountIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Camt\GenericIdentification;
use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\AccountIdentificationType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AccountIdentificationTest extends TestCase {
    #[Test]
    public function constructorWithMinimalParameters(): void {
        $account = new AccountIdentification();

        $this->assertNull($account->getIban());
        $this->assertNull($account->getOtherId());
    }

    #[Test]
    public function constructorWithIban(): void {
        $account = new AccountIdentification(iban: 'DE89370400440532013000');

        $this->assertSame('DE89370400440532013000', $account->getIban());
        $this->assertNull($account->getOtherId());
        $this->assertTrue($account->isIban());
    }

    #[Test]
    public function constructorWithOtherId(): void {
        $otherId = new GenericIdentification('12345678', schemeCode: 'BBAN');
        $account = new AccountIdentification(otherId: $otherId);

        $this->assertNull($account->getIban());
        $this->assertSame($otherId, $account->getOtherId());
        $this->assertFalse($account->isIban());
    }

    #[Test]
    public function constructorWithBothIbanAndOtherId(): void {
        $otherId = new GenericIdentification('12345678', schemeCode: 'BBAN');
        $account = new AccountIdentification(
            iban: 'DE89370400440532013000',
            otherId: $otherId
        );

        $this->assertSame('DE89370400440532013000', $account->getIban());
        $this->assertSame($otherId, $account->getOtherId());
        $this->assertTrue($account->isIban());
    }

    #[Test]
    public function isIbanReturnsTrueWhenIbanSet(): void {
        $account = new AccountIdentification(iban: 'DE89370400440532013000');

        $this->assertTrue($account->isIban());
    }

    #[Test]
    public function isIbanReturnsFalseWhenOnlyOtherId(): void {
        $otherId = new GenericIdentification('12345678');
        $account = new AccountIdentification(otherId: $otherId);

        $this->assertFalse($account->isIban());
    }

    #[Test]
    public function getIdReturnsIbanWhenSet(): void {
        $otherId = new GenericIdentification('12345678');
        $account = new AccountIdentification(
            iban: 'DE89370400440532013000',
            otherId: $otherId
        );

        $this->assertSame('DE89370400440532013000', $account->getId());
    }

    #[Test]
    public function getIdReturnsOtherIdWhenNoIban(): void {
        $otherId = new GenericIdentification('12345678');
        $account = new AccountIdentification(otherId: $otherId);

        $this->assertSame('12345678', $account->getId());
    }

    #[Test]
    public function getIdReturnsEmptyStringWhenEmpty(): void {
        $account = new AccountIdentification();

        $this->assertSame('', $account->getId());
    }

    #[Test]
    public function getSchemeNameReturnsIbanWhenIbanSet(): void {
        $account = new AccountIdentification(iban: 'DE89370400440532013000');

        $this->assertSame('IBAN', $account->getSchemeName());
    }

    #[Test]
    public function getSchemeNameReturnsSchemeCodeFromOtherId(): void {
        $otherId = new GenericIdentification('12345678', schemeCode: 'BBAN');
        $account = new AccountIdentification(otherId: $otherId);

        $this->assertSame('BBAN', $account->getSchemeName());
    }

    #[Test]
    public function getSchemeNameReturnsProprietaryFromOtherId(): void {
        $otherId = new GenericIdentification('12345678', schemeProprietary: 'CustomScheme');
        $account = new AccountIdentification(otherId: $otherId);

        $this->assertSame('CustomScheme', $account->getSchemeName());
    }

    #[Test]
    public function getSchemeNameReturnsNullWhenEmpty(): void {
        $account = new AccountIdentification();

        $this->assertNull($account->getSchemeName());
    }

    #[Test]
    public function getAccountIdentificationType(): void {
        $otherId = new GenericIdentification('12345678', schemeCode: 'BBAN');
        $account = new AccountIdentification(otherId: $otherId);

        $type = $account->getAccountIdentificationType();

        $this->assertInstanceOf(AccountIdentificationType::class, $type);
        $this->assertSame(AccountIdentificationType::BBAN, $type);
    }

    #[Test]
    public function getAccountIdentificationTypeReturnsNullWithIban(): void {
        $account = new AccountIdentification(iban: 'DE89370400440532013000');

        $this->assertNull($account->getAccountIdentificationType());
    }

    #[Test]
    public function getAccountIdentificationTypeReturnsNullWhenEmpty(): void {
        $account = new AccountIdentification();

        $this->assertNull($account->getAccountIdentificationType());
    }

    #[Test]
    public function toStringReturnsIban(): void {
        $account = new AccountIdentification(iban: 'DE89370400440532013000');

        $string = (string) $account;

        $this->assertSame('DE89370400440532013000', $string);
    }

    #[Test]
    public function toStringReturnsOtherId(): void {
        $otherId = new GenericIdentification('12345678');
        $account = new AccountIdentification(otherId: $otherId);

        $string = (string) $account;

        $this->assertSame('12345678', $string);
    }

    #[Test]
    public function toStringReturnsEmptyString(): void {
        $account = new AccountIdentification();

        $string = (string) $account;

        $this->assertSame('', $string);
    }
}
