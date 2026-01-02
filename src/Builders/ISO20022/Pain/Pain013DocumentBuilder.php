<?php
/*
 * Created on   : Thu Jan 02 2026
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Pain013DocumentBuilder.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Builders\ISO20022\Pain;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type13\Document;
use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type13\PaymentActivationRequest;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Builder für pain.013 Documents (Creditor Payment Activation Request).
 * 
 * Erstellt Anfragen zur Aktivierung von Zahlungen durch den Gläubiger.
 * Der Gläubiger initiiert eine Zahlung, die der Schuldner bestätigen muss.
 * 
 * Verwendung:
 * ```php
 * $document = Pain013DocumentBuilder::create('MSG-001', 'Firma GmbH')
 *     ->addPaymentRequest(PaymentActivationRequest::create(
 *         'E2E-001', 100.00, 'Schuldner', 'DE91...', 'DEUTDEFF',
 *         'Gläubiger', 'DE89...', 'COBADEFF', 'Rechnung 123'
 *     ))
 *     ->build();
 * ```
 * 
 * @package CommonToolkit\FinancialFormats\Builders\Pain
 */
final class Pain013DocumentBuilder {
    private string $messageId;
    private DateTimeImmutable $creationDateTime;
    private PartyIdentification $initiatingParty;
    /** @var PaymentActivationRequest[] */
    private array $paymentRequests = [];

    private function __construct(string $messageId, PartyIdentification $initiatingParty) {
        if (strlen($messageId) > 35) {
            throw new InvalidArgumentException('MsgId darf maximal 35 Zeichen lang sein');
        }
        $this->messageId = $messageId;
        $this->creationDateTime = new DateTimeImmutable();
        $this->initiatingParty = $initiatingParty;
    }

    /**
     * Erzeugt neuen Builder mit Message-ID und Initiator-Name.
     */
    public static function create(string $messageId, string $initiatingPartyName): self {
        return new self($messageId, new PartyIdentification(name: $initiatingPartyName));
    }

    /**
     * Erzeugt neuen Builder mit vollständiger PartyIdentification.
     */
    public static function createWithParty(string $messageId, PartyIdentification $initiatingParty): self {
        return new self($messageId, $initiatingParty);
    }

    /**
     * Setzt den Erstellungszeitpunkt (Standard: jetzt).
     */
    public function withCreationDateTime(DateTimeImmutable $dateTime): self {
        $clone = clone $this;
        $clone->creationDateTime = $dateTime;
        return $clone;
    }

    /**
     * Fügt eine Zahlungsaktivierungsanfrage hinzu.
     */
    public function addPaymentRequest(PaymentActivationRequest $request): self {
        $clone = clone $this;
        $clone->paymentRequests[] = $request;
        return $clone;
    }

    /**
     * Convenience: Fügt eine einfache Zahlungsanfrage hinzu.
     */
    public function addSimpleRequest(
        string $endToEndId,
        float $amount,
        string $debtorName,
        string $debtorIban,
        string $debtorBic,
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        ?string $remittanceInformation = null
    ): self {
        return $this->addPaymentRequest(
            PaymentActivationRequest::create(
                $endToEndId,
                $amount,
                $debtorName,
                $debtorIban,
                $debtorBic,
                $creditorName,
                $creditorIban,
                $creditorBic,
                $remittanceInformation
            )
        );
    }

    /**
     * Fügt mehrere Zahlungsanfragen hinzu.
     * 
     * @param PaymentActivationRequest[] $requests
     */
    public function addPaymentRequests(array $requests): self {
        $clone = clone $this;
        $clone->paymentRequests = array_merge($clone->paymentRequests, $requests);
        return $clone;
    }

    /**
     * Erstellt das pain.013 Dokument.
     * 
     * @throws InvalidArgumentException wenn keine Requests vorhanden
     */
    public function build(): Document {
        if (empty($this->paymentRequests)) {
            throw new InvalidArgumentException('Mindestens eine Zahlungsaktivierungsanfrage erforderlich');
        }

        return Document::create(
            messageId: $this->messageId,
            initiatingParty: $this->initiatingParty,
            paymentRequests: $this->paymentRequests
        );
    }

    // === Static Factory Methods ===

    /**
     * Erstellt eine einfache Zahlungsaktivierungsanfrage.
     */
    public static function createSingleRequest(
        string $messageId,
        string $endToEndId,
        float $amount,
        string $debtorName,
        string $debtorIban,
        string $debtorBic,
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        ?string $remittanceInformation = null
    ): Document {
        return self::create($messageId, $creditorName)
            ->addSimpleRequest(
                $endToEndId,
                $amount,
                $debtorName,
                $debtorIban,
                $debtorBic,
                $creditorName,
                $creditorIban,
                $creditorBic,
                $remittanceInformation
            )
            ->build();
    }
}
