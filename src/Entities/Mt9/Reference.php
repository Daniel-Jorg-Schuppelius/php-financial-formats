<?php
/*
 * Created on   : Sat Dec 27 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Reference.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Mt9;

use CommonToolkit\FinancialFormats\Enums\Mt\TransactionTypeIndicator;
use RuntimeException;

/**
 * Common reference class for all MT9xx message types.
 * 
 * Represents the reference in Field :61: of a SWIFT MT message.
 * Format: [Buchungsschluessel][Transaktionscode 3 Zeichen][Referenz max. 16 Zeichen]//[Bankreferenz]
 * 
 * @package CommonToolkit\Entities\Common\Banking\Mt9
 */
class Reference {
    private TransactionTypeIndicator $bookingKey;
    private string $transactionCode;
    private string $reference;
    private ?string $bankReference;

    /**
     * @param string $transactionCode 3-stelliger Transaktionscode (z.B. TRF, CHK)
     * @param string $reference Kundenreferenz (max. 16 Zeichen)
     * @param string|null $bankReference Bankreferenz nach //
     * @param TransactionTypeIndicator|string $bookingKey Buchungsschluessel (N, F, S)
     */
    public function __construct(
        string $transactionCode,
        string $reference,
        ?string $bankReference = null,
        TransactionTypeIndicator|string $bookingKey = TransactionTypeIndicator::SWIFT
    ) {
        $code = strtoupper($transactionCode);
        if (strlen($code) > 3) {
            $code = substr($code, 0, 3);
        }
        if (strlen($code) !== 3 || !preg_match('/^[A-Z0-9]{3}$/', $code)) {
            throw new RuntimeException("MT9xx-Transaktionscode ungültig: $transactionCode");
        }

        // BookingKey als Enum verarbeiten
        if ($bookingKey instanceof TransactionTypeIndicator) {
            $this->bookingKey = $bookingKey;
        } else {
            $bookingKeyUpper = strtoupper($bookingKey);
            $this->bookingKey = TransactionTypeIndicator::tryFrom($bookingKeyUpper)
                ?? throw new RuntimeException("MT9xx-Buchungsschluessel ungültig: $bookingKey");
        }

        if (strlen($reference) > 16) {
            throw new RuntimeException("MT9xx-Referenzüberschreitung: max. 16 Zeichen erlaubt, übergeben: " . $reference);
        }
        if ($bankReference !== null && strlen($bankReference) > 16) {
            throw new RuntimeException("MT9xx-Bankreferenzüberschreitung: max. 16 Zeichen erlaubt, übergeben: " . $bankReference);
        }

        $this->transactionCode = $code;
        $this->reference = $reference;
        $this->bankReference = $bankReference;
    }

    /**
     * Returns the transaction code (e.g. TRF, CHK, BOE).
     */
    public function getTransactionCode(): string {
        return $this->transactionCode;
    }

    /**
     * Returns the customer reference.
     */
    public function getReference(): string {
        return $this->reference;
    }

    /**
     * Returns the booking key enum (Subfield 6 in :61:).
     */
    public function getBookingKey(): TransactionTypeIndicator {
        return $this->bookingKey;
    }

    /**
     * Returns the combined booking key and transaction code (e.g., "N051").
     */
    public function getBookingKeyWithCode(): string {
        return $this->bookingKey->value . $this->transactionCode;
    }

    /**
     * Returns the bank reference (after //).
     */
    public function getBankReference(): ?string {
        return $this->bankReference;
    }

    /**
     * Serialisiert im SWIFT MT-Format.
     * Format: [Buchungsschluessel][Code][Referenz]//[Bankreferenz]
     */
    public function __toString(): string {
        $result = $this->getBookingKeyWithCode() . $this->reference;
        if ($this->bankReference !== null) {
            $result .= '//' . $this->bankReference;
        }
        return $result;
    }

    /**
     * Parst eine Referenz aus einem SWIFT MT :61: Feld.
     */
    public static function fromSwiftField(string $field): self {
        $bankReference = null;

        // Bankreferenz nach // extrahieren
        if (str_contains($field, '//')) {
            [$field, $bankReference] = explode('//', $field, 2);
        }

        // N + 3-stelliger Code + Referenz
        $validKeys = [TransactionTypeIndicator::SWIFT->value, TransactionTypeIndicator::FIRST_ADVICE->value, TransactionTypeIndicator::OTHER->value];
        if (preg_match('/^([A-Z])([A-Z0-9]{3})(.*)$/', $field, $matches) && in_array($matches[1], $validKeys, true)) {
            return new self($matches[2], $matches[3], $bankReference, $matches[1]);
        }

        if (preg_match('/^([A-Z0-9]{3})(.*)$/', $field, $matches)) {
            return new self($matches[1], $matches[2], $bankReference);
        }

        // Fallback: Alles als Referenz
        return new self('TRF', $field, $bankReference);
    }
}