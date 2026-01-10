<?php
/*
 * Created on   : Thu Jan 09 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : DocumentLinkTest.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace Tests\Entities\DATEV;

use CommonToolkit\FinancialFormats\Entities\DATEV\DocumentLink;
use CommonToolkit\FinancialFormats\Enums\DATEV\DocumentLinkType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DocumentLinkTest extends TestCase {
    #[Test]
    public function constructorWithValidGuid(): void {
        $link = new DocumentLink(
            DocumentLinkType::BEDI,
            '12345678-1234-1234-1234-123456789ABC'
        );

        $this->assertSame(DocumentLinkType::BEDI, $link->type);
        $this->assertSame('12345678-1234-1234-1234-123456789ABC', $link->guid);
    }

    #[Test]
    public function constructorWithInvalidGuidThrowsException(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ungültige GUID');

        new DocumentLink(DocumentLinkType::BEDI, 'invalid-guid');
    }

    #[Test]
    public function toDatevString(): void {
        $link = new DocumentLink(
            DocumentLinkType::BEDI,
            '12345678-1234-1234-1234-123456789ABC'
        );

        $result = $link->toDatevString();

        $this->assertStringContainsString('BEDI', $result);
        $this->assertStringContainsString('12345678-1234-1234-1234-123456789ABC', $result);
    }

    #[Test]
    public function parseInvalidLinkThrowsException(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ungültiger Beleglink');

        DocumentLink::parse('INVALID "not-a-guid"');
    }

    #[Test]
    public function toStringReturnsDatevString(): void {
        $link = new DocumentLink(
            DocumentLinkType::BEDI,
            '12345678-1234-1234-1234-123456789ABC'
        );

        $string = (string) $link;

        $this->assertSame($link->toDatevString(), $string);
    }

    #[Test]
    public function guidCaseInsensitive(): void {
        $link = new DocumentLink(
            DocumentLinkType::BEDI,
            'abcdef12-3456-7890-abcd-ef1234567890'
        );

        $this->assertSame('abcdef12-3456-7890-abcd-ef1234567890', $link->guid);
    }
}
