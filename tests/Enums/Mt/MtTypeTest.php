<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : MtTypeTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Enums\Mt;

use CommonToolkit\FinancialFormats\Enums\ISO20022\Camt\CamtType;
use CommonToolkit\FinancialFormats\Enums\Mt\MtType;
use Tests\Contracts\BaseTestCase;

class MtTypeTest extends BaseTestCase {
    public function testAllCasesExist(): void {
        $expectedCases = [
            // Category 1: Customer Payments
            'MT101',
            'MT102',
            'MT103',
            'MT103STP',
            'MT104',
            'MT105',
            'MT107',
            'MT110',
            'MT111',
            'MT112',
            'MT190',
            'MT191',
            'MT192',
            'MT195',
            'MT196',
            'MT199',
            // Category 2: FI Transfers
            'MT200',
            'MT201',
            'MT202',
            'MT202COV',
            'MT203',
            'MT204',
            'MT205',
            'MT205COV',
            'MT210',
            'MT290',
            'MT291',
            'MT292',
            'MT295',
            'MT296',
            'MT299',
            // Category 5: Securities
            'MT502',
            'MT509',
            'MT513',
            'MT515',
            'MT518',
            'MT535',
            'MT536',
            'MT537',
            'MT540',
            'MT541',
            'MT542',
            'MT543',
            'MT544',
            'MT545',
            'MT546',
            'MT547',
            'MT548',
            'MT564',
            'MT565',
            'MT566',
            'MT567',
            'MT568',
            'MT569',
            'MT574',
            'MT575',
            'MT576',
            'MT578',
            'MT586',
            'MT592',
            'MT595',
            'MT596',
            'MT599',
            // Category 7: Documentary Credits
            'MT700',
            'MT701',
            'MT705',
            'MT707',
            'MT710',
            'MT711',
            'MT720',
            'MT721',
            'MT730',
            'MT732',
            'MT734',
            'MT740',
            'MT742',
            'MT747',
            'MT750',
            'MT752',
            'MT754',
            'MT756',
            'MT760',
            'MT765',
            'MT767',
            'MT768',
            'MT769',
            'MT790',
            'MT791',
            'MT792',
            'MT795',
            'MT796',
            'MT799',
            // Category 9: Cash Management
            'MT900',
            'MT910',
            'MT920',
            'MT940',
            'MT941',
            'MT942',
            'MT950',
            'MT970',
            'MT971',
            'MT972',
            'MT973',
            'MT985',
            'MT986',
            'MT990',
            'MT991',
            'MT992',
            'MT995',
            'MT996',
            'MT999',
        ];

        $actualCases = array_map(fn($case) => $case->name, MtType::cases());

        foreach ($expectedCases as $expected) {
            $this->assertContains($expected, $actualCases, "Expected case $expected to exist");
        }
        $this->assertCount(111, MtType::cases());
    }

    public function testGetDescription(): void {
        $this->assertStringContainsString('Zahlungsauftrag', MtType::MT101->getDescription());
        $this->assertStringContainsString('Sammelüberweisung', MtType::MT102->getDescription());
        $this->assertStringContainsString('Einzelüberweisung', MtType::MT103->getDescription());
        $this->assertStringContainsString('Kundenlastschrift', MtType::MT104->getDescription());
        $this->assertStringContainsString('Eigengeschäft', MtType::MT200->getDescription());
        $this->assertStringContainsString('Institutsüberweisung', MtType::MT202->getDescription());
        $this->assertStringContainsString('Cover', MtType::MT202COV->getDescription());
        $this->assertStringContainsString('Statement', MtType::MT950->getDescription());
    }

    public function testGetMessageName(): void {
        $this->assertEquals('Request for Transfer', MtType::MT101->getMessageName());
        $this->assertEquals('Multiple Customer Credit Transfer', MtType::MT102->getMessageName());
        $this->assertEquals('Single Customer Credit Transfer', MtType::MT103->getMessageName());
        $this->assertEquals('Customer Direct Debit', MtType::MT104->getMessageName());
        $this->assertEquals('Financial Institution Transfer for its Own Account', MtType::MT200->getMessageName());
        $this->assertEquals('General Financial Institution Transfer', MtType::MT202->getMessageName());
        $this->assertEquals('Cover Payment', MtType::MT202COV->getMessageName());
        $this->assertEquals('Statement Message', MtType::MT950->getMessageName());
    }

    public function testGetNumericType(): void {
        $this->assertEquals(101, MtType::MT101->getNumericType());
        $this->assertEquals(102, MtType::MT102->getNumericType());
        $this->assertEquals(103, MtType::MT103->getNumericType());
        $this->assertEquals(104, MtType::MT104->getNumericType());
        $this->assertEquals(200, MtType::MT200->getNumericType());
        $this->assertEquals(202, MtType::MT202->getNumericType());
        $this->assertEquals(202, MtType::MT202COV->getNumericType()); // Same numeric as MT202
        $this->assertEquals(940, MtType::MT940->getNumericType());
        $this->assertEquals(950, MtType::MT950->getNumericType());
    }

    public function testGetCategory(): void {
        // Category 1: Customer Payments
        $this->assertEquals(1, MtType::MT101->getCategory());
        $this->assertEquals(1, MtType::MT102->getCategory());
        $this->assertEquals(1, MtType::MT103->getCategory());
        $this->assertEquals(1, MtType::MT104->getCategory());

        // Category 2: FI Transfers
        $this->assertEquals(2, MtType::MT200->getCategory());
        $this->assertEquals(2, MtType::MT202->getCategory());
        $this->assertEquals(2, MtType::MT202COV->getCategory());
        $this->assertEquals(2, MtType::MT203->getCategory());
        $this->assertEquals(2, MtType::MT204->getCategory());

        // Category 9: Cash Management
        $this->assertEquals(9, MtType::MT900->getCategory());
        $this->assertEquals(9, MtType::MT940->getCategory());
        $this->assertEquals(9, MtType::MT950->getCategory());
    }

    public function testGetCategoryDescription(): void {
        $this->assertEquals('Customer Payments and Cheques', MtType::MT101->getCategoryDescription());
        $this->assertEquals('Financial Institution Transfers', MtType::MT202->getCategoryDescription());
        $this->assertEquals('Cash Management and Customer Status', MtType::MT940->getCategoryDescription());
    }

    public function testIsPaymentInitiation(): void {
        $this->assertTrue(MtType::MT101->isPaymentInitiation());
        $this->assertTrue(MtType::MT102->isPaymentInitiation());
        $this->assertTrue(MtType::MT103->isPaymentInitiation());
        $this->assertTrue(MtType::MT104->isPaymentInitiation());

        $this->assertFalse(MtType::MT200->isPaymentInitiation());
        $this->assertFalse(MtType::MT940->isPaymentInitiation());
    }

    public function testIsConfirmation(): void {
        $this->assertTrue(MtType::MT900->isConfirmation());
        $this->assertTrue(MtType::MT910->isConfirmation());

        $this->assertFalse(MtType::MT101->isConfirmation());
        $this->assertFalse(MtType::MT940->isConfirmation());
    }

    public function testIsStatement(): void {
        $this->assertTrue(MtType::MT940->isStatement());
        $this->assertTrue(MtType::MT941->isStatement());
        $this->assertTrue(MtType::MT942->isStatement());
        $this->assertTrue(MtType::MT950->isStatement());

        $this->assertFalse(MtType::MT101->isStatement());
        $this->assertFalse(MtType::MT900->isStatement());
    }

    public function testHasTransactions(): void {
        // Payment types have transactions
        $this->assertTrue(MtType::MT101->hasTransactions());
        $this->assertTrue(MtType::MT102->hasTransactions());
        $this->assertTrue(MtType::MT103->hasTransactions());
        $this->assertTrue(MtType::MT104->hasTransactions());

        // FI Transfer types have transactions
        $this->assertTrue(MtType::MT200->hasTransactions());
        $this->assertTrue(MtType::MT202->hasTransactions());
        $this->assertTrue(MtType::MT202COV->hasTransactions());

        // Statements have transactions
        $this->assertTrue(MtType::MT940->hasTransactions());
        $this->assertTrue(MtType::MT942->hasTransactions());
        $this->assertTrue(MtType::MT950->hasTransactions());

        // Confirmations and balance reports don't have transactions
        $this->assertFalse(MtType::MT900->hasTransactions());
        $this->assertFalse(MtType::MT910->hasTransactions());
        $this->assertFalse(MtType::MT941->hasTransactions());
    }

    public function testHasBalances(): void {
        $this->assertTrue(MtType::MT940->hasBalances());
        $this->assertTrue(MtType::MT941->hasBalances());
        $this->assertTrue(MtType::MT942->hasBalances());
        $this->assertTrue(MtType::MT950->hasBalances());

        $this->assertFalse(MtType::MT101->hasBalances());
        $this->assertFalse(MtType::MT202->hasBalances());
        $this->assertFalse(MtType::MT900->hasBalances());
    }

    public function testGetCamtEquivalent(): void {
        $this->assertEquals(CamtType::CAMT053, MtType::MT940->getCamtEquivalent());
        $this->assertEquals(CamtType::CAMT053, MtType::MT950->getCamtEquivalent());
        $this->assertEquals(CamtType::CAMT052, MtType::MT942->getCamtEquivalent());
        $this->assertEquals(CamtType::CAMT054, MtType::MT900->getCamtEquivalent());
        $this->assertEquals(CamtType::CAMT054, MtType::MT910->getCamtEquivalent());

        $this->assertNull(MtType::MT101->getCamtEquivalent());
        $this->assertNull(MtType::MT202->getCamtEquivalent());
    }

    public function testFromNumeric(): void {
        $this->assertEquals(MtType::MT101, MtType::fromNumeric(101));
        $this->assertEquals(MtType::MT102, MtType::fromNumeric(102));
        $this->assertEquals(MtType::MT103, MtType::fromNumeric(103));
        $this->assertEquals(MtType::MT104, MtType::fromNumeric(104));
        $this->assertEquals(MtType::MT200, MtType::fromNumeric(200));
        $this->assertEquals(MtType::MT202, MtType::fromNumeric(202));
        $this->assertEquals(MtType::MT535, MtType::fromNumeric(535));
        $this->assertEquals(MtType::MT700, MtType::fromNumeric(700));
        $this->assertEquals(MtType::MT940, MtType::fromNumeric(940));
        $this->assertEquals(MtType::MT950, MtType::fromNumeric(950));

        $this->assertNull(MtType::fromNumeric(888));
    }

    public function testGetStatementTypes(): void {
        $statementTypes = MtType::getStatementTypes();

        $this->assertContains(MtType::MT940, $statementTypes);
        $this->assertContains(MtType::MT941, $statementTypes);
        $this->assertContains(MtType::MT942, $statementTypes);
        $this->assertContains(MtType::MT950, $statementTypes);
        $this->assertContains(MtType::MT970, $statementTypes);
        $this->assertContains(MtType::MT971, $statementTypes);
        $this->assertContains(MtType::MT972, $statementTypes);
        $this->assertCount(7, $statementTypes);
    }

    public function testGetPaymentTypes(): void {
        $paymentTypes = MtType::getPaymentTypes();

        $this->assertContains(MtType::MT101, $paymentTypes);
        $this->assertContains(MtType::MT102, $paymentTypes);
        $this->assertContains(MtType::MT103, $paymentTypes);
        $this->assertContains(MtType::MT103STP, $paymentTypes);
        $this->assertContains(MtType::MT104, $paymentTypes);
        $this->assertContains(MtType::MT105, $paymentTypes);
        $this->assertContains(MtType::MT107, $paymentTypes);
        $this->assertCount(7, $paymentTypes);
    }

    public function testGetFITransferTypes(): void {
        $fiTypes = MtType::getFITransferTypes();

        $this->assertContains(MtType::MT200, $fiTypes);
        $this->assertContains(MtType::MT201, $fiTypes);
        $this->assertContains(MtType::MT202, $fiTypes);
        $this->assertContains(MtType::MT202COV, $fiTypes);
        $this->assertContains(MtType::MT203, $fiTypes);
        $this->assertContains(MtType::MT204, $fiTypes);
        $this->assertContains(MtType::MT205, $fiTypes);
        $this->assertContains(MtType::MT205COV, $fiTypes);
        $this->assertContains(MtType::MT210, $fiTypes);
        $this->assertCount(9, $fiTypes);
    }

    public function testGetConfirmationTypes(): void {
        $confirmationTypes = MtType::getConfirmationTypes();

        $this->assertContains(MtType::MT900, $confirmationTypes);
        $this->assertContains(MtType::MT910, $confirmationTypes);
        $this->assertCount(2, $confirmationTypes);
    }

    public function testGetSecuritiesTypes(): void {
        $securitiesTypes = MtType::getSecuritiesTypes();

        $this->assertContains(MtType::MT535, $securitiesTypes);
        $this->assertContains(MtType::MT540, $securitiesTypes);
        $this->assertContains(MtType::MT564, $securitiesTypes);

        foreach ($securitiesTypes as $type) {
            $this->assertEquals(5, $type->getCategory());
        }
    }

    public function testGetDocumentaryCreditTypes(): void {
        $dcTypes = MtType::getDocumentaryCreditTypes();

        $this->assertContains(MtType::MT700, $dcTypes);
        $this->assertContains(MtType::MT760, $dcTypes);
        $this->assertContains(MtType::MT799, $dcTypes);

        foreach ($dcTypes as $type) {
            $this->assertEquals(7, $type->getCategory());
        }
    }

    public function testGetChequeTypes(): void {
        $chequeTypes = MtType::getChequeTypes();

        $this->assertContains(MtType::MT110, $chequeTypes);
        $this->assertContains(MtType::MT111, $chequeTypes);
        $this->assertContains(MtType::MT112, $chequeTypes);
        $this->assertCount(3, $chequeTypes);
    }

    public function testIsCommonMessage(): void {
        // n9x messages are common messages
        $this->assertTrue(MtType::MT190->isCommonMessage());
        $this->assertTrue(MtType::MT192->isCommonMessage());
        $this->assertTrue(MtType::MT199->isCommonMessage());
        $this->assertTrue(MtType::MT299->isCommonMessage());
        $this->assertTrue(MtType::MT599->isCommonMessage());
        $this->assertTrue(MtType::MT799->isCommonMessage());
        $this->assertTrue(MtType::MT999->isCommonMessage());

        // Other messages are not common messages
        $this->assertFalse(MtType::MT101->isCommonMessage());
        $this->assertFalse(MtType::MT940->isCommonMessage());
    }

    public function testIsCancellationRequest(): void {
        $this->assertTrue(MtType::MT192->isCancellationRequest());
        $this->assertTrue(MtType::MT292->isCancellationRequest());
        $this->assertTrue(MtType::MT592->isCancellationRequest());
        $this->assertTrue(MtType::MT792->isCancellationRequest());
        $this->assertTrue(MtType::MT992->isCancellationRequest());

        $this->assertFalse(MtType::MT199->isCancellationRequest());
    }

    public function testIsFreeFormat(): void {
        $this->assertTrue(MtType::MT199->isFreeFormat());
        $this->assertTrue(MtType::MT299->isFreeFormat());
        $this->assertTrue(MtType::MT599->isFreeFormat());
        $this->assertTrue(MtType::MT799->isFreeFormat());
        $this->assertTrue(MtType::MT999->isFreeFormat());

        $this->assertFalse(MtType::MT192->isFreeFormat());
    }

    public function testIsSecuritiesMessage(): void {
        $this->assertTrue(MtType::MT535->isSecuritiesMessage());
        $this->assertTrue(MtType::MT540->isSecuritiesMessage());
        $this->assertTrue(MtType::MT564->isSecuritiesMessage());
        $this->assertTrue(MtType::MT599->isSecuritiesMessage());

        $this->assertFalse(MtType::MT101->isSecuritiesMessage());
        $this->assertFalse(MtType::MT700->isSecuritiesMessage());
    }

    public function testIsDocumentaryCredit(): void {
        $this->assertTrue(MtType::MT700->isDocumentaryCredit());
        $this->assertTrue(MtType::MT760->isDocumentaryCredit());
        $this->assertTrue(MtType::MT799->isDocumentaryCredit());

        $this->assertFalse(MtType::MT599->isDocumentaryCredit());
        $this->assertFalse(MtType::MT940->isDocumentaryCredit());
    }

    public function testCategory5GetCategoryDescription(): void {
        $this->assertEquals('Securities Markets', MtType::MT535->getCategoryDescription());
        $this->assertEquals('Securities Markets', MtType::MT564->getCategoryDescription());
    }

    public function testCategory7GetCategoryDescription(): void {
        $this->assertEquals('Documentary Credits and Guarantees', MtType::MT700->getCategoryDescription());
        $this->assertEquals('Documentary Credits and Guarantees', MtType::MT760->getCategoryDescription());
    }

    public function testFromSwiftMessageMt940(): void {
        $mt940Content = ":20:STARTUMS\r\n:25:12345678/0000000001\r\n:60F:C250109EUR1000,00\r\n:62F:C250109EUR1200,00\r\n";
        $type = MtType::fromSwiftMessage($mt940Content);

        $this->assertEquals(MtType::MT940, $type);
    }

    public function testFromSwiftMessageMt942(): void {
        $mt942Content = ":20:STARTUMS\r\n:25:12345678/0000000001\r\n:60M:C250109EUR1000,00\r\n";
        $type = MtType::fromSwiftMessage($mt942Content);

        $this->assertEquals(MtType::MT942, $type);
    }

    public function testFromSwiftMessageWithHeader(): void {
        $content = "{1:F01BANKDEFFXXXX0000000000}{2:O940BANKDEFFXXXX00000000002501090000N}{4:\r\n:20:STARTUMS\r\n-}";
        $type = MtType::fromSwiftMessage($content);

        $this->assertEquals(MtType::MT940, $type);
    }
}
