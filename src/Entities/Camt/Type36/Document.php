<?php

/*
 * Created on   : Tue Dec 31 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : Document.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Entities\Camt\Type36;

use CommonToolkit\FinancialFormats\Contracts\Interfaces\CamtDocumentInterface;
use CommonToolkit\FinancialFormats\Enums\CamtType;
use CommonToolkit\FinancialFormats\Enums\CamtVersion;
use CommonToolkit\Enums\CurrencyCode;
use DateTimeImmutable;
use DOMDocument;

/**
 * CAMT.036 Document (Debit Authorisation Response).
 *
 * Repräsentiert die Antwort auf eine Belastungsautorisierungsanfrage (CAMT.037)
 * gemäß ISO 20022 camt.036.001.xx Standard.
 *
 * Wird verwendet, um eine Belastung zu genehmigen oder abzulehnen.
 *
 * @package CommonToolkit\FinancialFormats\Entities\Camt\Type36
 */
class Document implements CamtDocumentInterface {
    protected string $assignmentId;
    protected DateTimeImmutable $creationDateTime;
    protected ?string $assignerAgentBic = null;
    protected ?string $assignerPartyName = null;
    protected ?string $assigneeAgentBic = null;
    protected ?string $assigneePartyName = null;
    protected ?string $caseId = null;
    protected ?string $caseCreator = null;

    // Confirmation
    protected bool $debitAuthorised = false;
    protected ?string $authorisedAmount = null;
    protected ?CurrencyCode $authorisedCurrency = null;
    protected ?DateTimeImmutable $valueDate = null;
    protected ?string $reason = null;

    public function __construct(
        string $assignmentId,
        DateTimeImmutable|string $creationDateTime,
        bool $debitAuthorised = false,
        ?string $assignerAgentBic = null,
        ?string $assignerPartyName = null,
        ?string $assigneeAgentBic = null,
        ?string $assigneePartyName = null,
        ?string $caseId = null,
        ?string $caseCreator = null,
        ?string $authorisedAmount = null,
        CurrencyCode|string|null $authorisedCurrency = null,
        DateTimeImmutable|string|null $valueDate = null,
        ?string $reason = null
    ) {
        $this->assignmentId = $assignmentId;
        $this->creationDateTime = $creationDateTime instanceof DateTimeImmutable
            ? $creationDateTime
            : new DateTimeImmutable($creationDateTime);
        $this->debitAuthorised = $debitAuthorised;
        $this->assignerAgentBic = $assignerAgentBic;
        $this->assignerPartyName = $assignerPartyName;
        $this->assigneeAgentBic = $assigneeAgentBic;
        $this->assigneePartyName = $assigneePartyName;
        $this->caseId = $caseId;
        $this->caseCreator = $caseCreator;
        $this->authorisedAmount = $authorisedAmount;
        $this->authorisedCurrency = $authorisedCurrency instanceof CurrencyCode
            ? $authorisedCurrency
            : ($authorisedCurrency !== null ? CurrencyCode::from($authorisedCurrency) : null);
        $this->valueDate = $valueDate instanceof DateTimeImmutable
            ? $valueDate
            : ($valueDate !== null ? new DateTimeImmutable($valueDate) : null);
        $this->reason = $reason;
    }

    public function getCamtType(): CamtType {
        return CamtType::CAMT036;
    }

    public function getAssignmentId(): string {
        return $this->assignmentId;
    }

    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    public function getAssignerAgentBic(): ?string {
        return $this->assignerAgentBic;
    }

    public function getAssignerPartyName(): ?string {
        return $this->assignerPartyName;
    }

    public function getAssigneeAgentBic(): ?string {
        return $this->assigneeAgentBic;
    }

    public function getAssigneePartyName(): ?string {
        return $this->assigneePartyName;
    }

    public function getCaseId(): ?string {
        return $this->caseId;
    }

    public function getCaseCreator(): ?string {
        return $this->caseCreator;
    }

    public function isDebitAuthorised(): bool {
        return $this->debitAuthorised;
    }

    public function getAuthorisedAmount(): ?string {
        return $this->authorisedAmount;
    }

    public function getAuthorisedCurrency(): ?CurrencyCode {
        return $this->authorisedCurrency;
    }

    public function getValueDate(): ?DateTimeImmutable {
        return $this->valueDate;
    }

    public function getReason(): ?string {
        return $this->reason;
    }

    public function toXml(CamtVersion $version = CamtVersion::V06): string {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $namespace = $version->getNamespace($this->getCamtType());
        $root = $dom->createElementNS($namespace, 'Document');
        $dom->appendChild($root);

        $dbtAuthstnRspn = $dom->createElement('DbtAuthstnRspn');
        $root->appendChild($dbtAuthstnRspn);

        // Assgnmt (Assignment)
        $assgnmt = $dom->createElement('Assgnmt');
        $dbtAuthstnRspn->appendChild($assgnmt);

        $assgnmt->appendChild($dom->createElement('Id', htmlspecialchars($this->assignmentId)));
        $assgnmt->appendChild($dom->createElement('CreDtTm', $this->creationDateTime->format('Y-m-d\TH:i:s.vP')));

        // Assigner
        if ($this->assignerAgentBic !== null || $this->assignerPartyName !== null) {
            $assgnr = $dom->createElement('Assgnr');
            $assgnmt->appendChild($assgnr);

            if ($this->assignerAgentBic !== null) {
                $agt = $dom->createElement('Agt');
                $assgnr->appendChild($agt);
                $finInstnId = $dom->createElement('FinInstnId');
                $agt->appendChild($finInstnId);
                $finInstnId->appendChild($dom->createElement('BICFI', htmlspecialchars($this->assignerAgentBic)));
            } elseif ($this->assignerPartyName !== null) {
                $pty = $dom->createElement('Pty');
                $assgnr->appendChild($pty);
                $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->assignerPartyName)));
            }
        }

        // Assignee
        if ($this->assigneeAgentBic !== null || $this->assigneePartyName !== null) {
            $assgne = $dom->createElement('Assgne');
            $assgnmt->appendChild($assgne);

            if ($this->assigneeAgentBic !== null) {
                $agt = $dom->createElement('Agt');
                $assgne->appendChild($agt);
                $finInstnId = $dom->createElement('FinInstnId');
                $agt->appendChild($finInstnId);
                $finInstnId->appendChild($dom->createElement('BICFI', htmlspecialchars($this->assigneeAgentBic)));
            } elseif ($this->assigneePartyName !== null) {
                $pty = $dom->createElement('Pty');
                $assgne->appendChild($pty);
                $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->assigneePartyName)));
            }
        }

        // Case
        if ($this->caseId !== null) {
            $case = $dom->createElement('Case');
            $dbtAuthstnRspn->appendChild($case);
            $case->appendChild($dom->createElement('Id', htmlspecialchars($this->caseId)));

            if ($this->caseCreator !== null) {
                $cretr = $dom->createElement('Cretr');
                $case->appendChild($cretr);
                $pty = $dom->createElement('Pty');
                $cretr->appendChild($pty);
                $pty->appendChild($dom->createElement('Nm', htmlspecialchars($this->caseCreator)));
            }
        }

        // Confirmation
        $conf = $dom->createElement('Conf');
        $dbtAuthstnRspn->appendChild($conf);

        $conf->appendChild($dom->createElement('DbtAuthstn', $this->debitAuthorised ? 'true' : 'false'));

        if ($this->authorisedAmount !== null && $this->authorisedCurrency !== null) {
            $amtToDbt = $dom->createElement('AmtToDbt', htmlspecialchars($this->authorisedAmount));
            $amtToDbt->setAttribute('Ccy', $this->authorisedCurrency->value);
            $conf->appendChild($amtToDbt);
        }

        if ($this->valueDate !== null) {
            $conf->appendChild($dom->createElement('ValDtToDbt', $this->valueDate->format('Y-m-d')));
        }

        if ($this->reason !== null) {
            $conf->appendChild($dom->createElement('Rsn', htmlspecialchars($this->reason)));
        }

        return $dom->saveXML() ?: '';
    }
}
