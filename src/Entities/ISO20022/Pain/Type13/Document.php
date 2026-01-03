<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\Type13;

use CommonToolkit\FinancialFormats\Entities\ISO20022\Pain\PartyIdentification;
use CommonToolkit\FinancialFormats\Enums\PainType;
use CommonToolkit\FinancialFormats\Generators\ISO20022\Pain\Pain013Generator;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;

/**
 * pain.013 Document - Creditor Payment Activation Request.
 * 
 * Request for payment activation by the creditor.
 * The creditor initiates a payment that the debtor must confirm.
 * 
 * @package CommonToolkit\Entities\Common\Banking\Pain\Type13
 */
final class Document {
    /** @var PaymentActivationRequest[] */
    private array $paymentRequests = [];

    public function __construct(
        private readonly string $messageId,
        private readonly DateTimeImmutable $creationDateTime,
        private readonly PartyIdentification $initiatingParty,
        private readonly int $numberOfTransactions = 0,
        private readonly float $controlSum = 0.0,
        array $paymentRequests = []
    ) {
        $this->paymentRequests = $paymentRequests;
    }

    public static function create(
        string $messageId,
        PartyIdentification $initiatingParty,
        array $paymentRequests = []
    ): self {
        $controlSum = array_reduce(
            $paymentRequests,
            fn($sum, PaymentActivationRequest $req) => $sum + $req->getAmount(),
            0.0
        );

        return new self(
            messageId: $messageId,
            creationDateTime: new DateTimeImmutable(),
            initiatingParty: $initiatingParty,
            numberOfTransactions: count($paymentRequests),
            controlSum: $controlSum,
            paymentRequests: $paymentRequests
        );
    }

    public function getType(): PainType {
        return PainType::PAIN_013;
    }

    public function getMessageId(): string {
        return $this->messageId;
    }

    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    public function getInitiatingParty(): PartyIdentification {
        return $this->initiatingParty;
    }

    public function getNumberOfTransactions(): int {
        return $this->numberOfTransactions;
    }

    public function getControlSum(): float {
        return $this->controlSum;
    }

    /**
     * @return PaymentActivationRequest[]
     */
    public function getPaymentRequests(): array {
        return $this->paymentRequests;
    }

    public function addPaymentRequest(PaymentActivationRequest $request): self {
        $newRequests = [...$this->paymentRequests, $request];
        return self::create($this->messageId, $this->initiatingParty, $newRequests);
    }

    public function countRequests(): int {
        return count($this->paymentRequests);
    }

    public function calculateSum(?CurrencyCode $currency = null): float {
        $sum = 0.0;
        foreach ($this->paymentRequests as $request) {
            if ($currency === null || $request->getCurrency() === $currency) {
                $sum += $request->getAmount();
            }
        }
        return $sum;
    }

    /**
     * @return array{valid: bool, errors: string[]}
     */
    public function validate(): array {
        $errors = [];

        if (strlen($this->messageId) > 35) {
            $errors[] = 'MsgId must not exceed 35 characters';
        }

        if (empty($this->paymentRequests)) {
            $errors[] = 'Mindestens eine Zahlungsaktivierungsanfrage erforderlich';
        }

        foreach ($this->paymentRequests as $index => $request) {
            if (strlen($request->getEndToEndId()) > 35) {
                $errors[] = "PmtActvtnReq[$index]/EndToEndId must not exceed 35 characters";
            }

            if ($request->getAmount() <= 0) {
                $errors[] = "PmtActvtnReq[$index]/Amt muss positiv sein";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Generates XML output for this document.
     *
     * @param string|null $namespace Optionaler XML-Namespace
     * @return string Das generierte XML
     */
    public function toXml(?string $namespace = null): string {
        $generator = $namespace !== null
            ? new Pain013Generator($namespace)
            : new Pain013Generator();
        return $generator->generate($this);
    }
}
