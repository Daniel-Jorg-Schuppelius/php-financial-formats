<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : GroupHeaderTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\ISO20022\Pain\Type1;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\FinancialInstitution;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type1\GroupHeader;
use DateTimeImmutable;
use Tests\Contracts\BaseTestCase;

class GroupHeaderTest extends BaseTestCase {
    private function createInitiatingParty(): PartyIdentification {
        return PartyIdentification::fromName('Test GmbH');
    }

    public function testConstructorWithAllFields(): void {
        $creationDateTime = new DateTimeImmutable('2025-01-15T10:30:00');
        $initiatingParty = $this->createInitiatingParty();
        $forwardingAgent = FinancialInstitution::fromBic('COBADEFFXXX');

        $header = new GroupHeader(
            messageId: 'MSG-2025-001',
            creationDateTime: $creationDateTime,
            numberOfTransactions: 5,
            initiatingParty: $initiatingParty,
            controlSum: 12500.50,
            forwardingAgent: $forwardingAgent
        );

        $this->assertSame('MSG-2025-001', $header->getMessageId());
        $this->assertEquals($creationDateTime, $header->getCreationDateTime());
        $this->assertSame(5, $header->getNumberOfTransactions());
        $this->assertSame($initiatingParty, $header->getInitiatingParty());
        $this->assertSame(12500.50, $header->getControlSum());
        $this->assertSame($forwardingAgent, $header->getForwardingAgent());
    }

    public function testConstructorWithMinimalFields(): void {
        $header = new GroupHeader(
            messageId: 'MSG-001',
            creationDateTime: new DateTimeImmutable(),
            numberOfTransactions: 1,
            initiatingParty: $this->createInitiatingParty()
        );

        $this->assertSame('MSG-001', $header->getMessageId());
        $this->assertSame(1, $header->getNumberOfTransactions());
        $this->assertNull($header->getControlSum());
        $this->assertNull($header->getForwardingAgent());
    }

    public function testWithTransactionCount(): void {
        $header = new GroupHeader(
            messageId: 'MSG-001',
            creationDateTime: new DateTimeImmutable(),
            numberOfTransactions: 1,
            initiatingParty: $this->createInitiatingParty(),
            controlSum: 100.00
        );

        $newHeader = $header->withTransactionCount(10);

        // Original unverändert
        $this->assertSame(1, $header->getNumberOfTransactions());
        // Neues Objekt mit neuem Wert
        $this->assertSame(10, $newHeader->getNumberOfTransactions());
        // Andere Werte beibehalten
        $this->assertSame('MSG-001', $newHeader->getMessageId());
        $this->assertSame(100.00, $newHeader->getControlSum());
    }

    public function testWithControlSum(): void {
        $header = new GroupHeader(
            messageId: 'MSG-001',
            creationDateTime: new DateTimeImmutable(),
            numberOfTransactions: 1,
            initiatingParty: $this->createInitiatingParty()
        );

        $newHeader = $header->withControlSum(5000.00);

        $this->assertNull($header->getControlSum());
        $this->assertSame(5000.00, $newHeader->getControlSum());
    }

    public function testReadonlyClass(): void {
        $header = new GroupHeader(
            messageId: 'MSG-001',
            creationDateTime: new DateTimeImmutable(),
            numberOfTransactions: 1,
            initiatingParty: $this->createInitiatingParty()
        );

        $reflection = new \ReflectionClass($header);
        $this->assertTrue($reflection->isReadOnly());
    }
}
