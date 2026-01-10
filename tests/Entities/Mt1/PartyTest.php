<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PartyTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Tests\Entities\Mt1;

use CommonToolkit\FinancialFormats\Entities\Mt1\Party;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PartyTest extends TestCase {
    #[Test]
    public function constructorWithMinimalParameters(): void {
        $party = new Party();

        $this->assertNull($party->getAccount());
        $this->assertNull($party->getBic());
        $this->assertNull($party->getName());
    }

    #[Test]
    public function constructorWithAllParameters(): void {
        $party = new Party(
            account: 'DE89370400440532013000',
            bic: 'DEUTDEFF',
            name: 'Max Mustermann GmbH',
            addressLine1: 'Musterstraße 1',
            addressLine2: '12345 Musterstadt',
            addressLine3: 'Deutschland'
        );

        $this->assertSame('DE89370400440532013000', $party->getAccount());
        $this->assertSame('DEUTDEFF', $party->getBic());
        $this->assertSame('Max Mustermann GmbH', $party->getName());
        $this->assertSame('Musterstraße 1', $party->getAddressLine1());
        $this->assertSame('12345 Musterstadt', $party->getAddressLine2());
        $this->assertSame('Deutschland', $party->getAddressLine3());
    }

    #[Test]
    public function getAddressLinesFiltersNull(): void {
        $party = new Party(
            addressLine1: 'Line 1',
            addressLine2: null,
            addressLine3: 'Line 3'
        );

        $lines = $party->getAddressLines();

        $this->assertCount(2, $lines);
        $this->assertContains('Line 1', $lines);
        $this->assertContains('Line 3', $lines);
    }

    #[Test]
    public function getAddressLinesEmpty(): void {
        $party = new Party();

        $lines = $party->getAddressLines();

        $this->assertSame([], $lines);
    }

    #[Test]
    public function getFullAddress(): void {
        $party = new Party(
            addressLine1: 'Musterstraße 1',
            addressLine2: '12345 Musterstadt',
            addressLine3: 'Deutschland'
        );

        $fullAddress = $party->getFullAddress();

        $this->assertStringContainsString('Musterstraße 1', $fullAddress);
        $this->assertStringContainsString('12345 Musterstadt', $fullAddress);
        $this->assertStringContainsString('Deutschland', $fullAddress);
    }

    #[Test]
    public function hasAccountReturnsTrue(): void {
        $party = new Party(account: 'DE89370400440532013000');

        $this->assertTrue($party->hasAccount());
    }

    #[Test]
    public function hasAccountReturnsFalse(): void {
        $party = new Party();

        $this->assertFalse($party->hasAccount());
    }

    #[Test]
    public function hasNameAddressReturnsTrue(): void {
        $party = new Party(name: 'Max', addressLine1: 'Some Address');

        $this->assertTrue($party->hasNameAddress());
    }

    #[Test]
    public function hasNameAddressReturnsFalse(): void {
        $party = new Party(bic: 'DEUTDEFF');

        $this->assertFalse($party->hasNameAddress());
    }
}
