<?php
/*
 * Created on   : Mon Dec 30 2025
 * Author       : Daniel Jörg Schuppelius
 * Author Uri   : https://schuppelius.org
 * Filename     : PainType.php
 * License      : AGPL-3.0-or-later
 * License Uri  : https://www.gnu.org/licenses/agpl-3.0.html
 */

declare(strict_types=1);

namespace CommonToolkit\FinancialFormats\Enums\ISO20022\Pain;

/**
 * ISO 20022 Payment Initiation (pain) Message Types.
 * 
 * @package CommonToolkit\Enums\Common\Banking
 */
enum PainType: string {
    /** Customer Credit Transfer Initiation - Überweisungsauftrag */
    case PAIN_001 = 'pain.001';

    /** Customer Payment Status Report - Statusbericht */
    case PAIN_002 = 'pain.002';

    /** Customer Payment Reversal - Rückrufauftrag */
    case PAIN_007 = 'pain.007';

    /** Customer Direct Debit Initiation - Lastschriftauftrag */
    case PAIN_008 = 'pain.008';

    /** Mandate Initiation Request - SEPA-Mandatsanfrage */
    case PAIN_009 = 'pain.009';

    /** Mandate Amendment Request - SEPA-Mandatsänderung */
    case PAIN_010 = 'pain.010';

    /** Mandate Cancellation Request - SEPA-Mandatskündigung */
    case PAIN_011 = 'pain.011';

    /** Mandate Acceptance Report - SEPA-Mandatsakzeptanz */
    case PAIN_012 = 'pain.012';

    /** Creditor Payment Activation Request - Creditor-initiated Payment */
    case PAIN_013 = 'pain.013';

    /** Creditor Payment Activation Request Status Report */
    case PAIN_014 = 'pain.014';

    /** Mandate Copy Request */
    case PAIN_017 = 'pain.017';

    /** Mandate Suspension Request */
    case PAIN_018 = 'pain.018';

    /**
     * Returns the namespace prefix using the default version.
     * 
     * @deprecated Use getNamespace(PainVersion) for explicit version control.
     */
    public function namespacePrefix(): string {
        return PainVersion::getDefault($this)->getNamespace($this);
    }

    /**
     * Returns the namespace for a specific version.
     */
    public function getNamespace(?PainVersion $version = null): string {
        $version ??= PainVersion::getDefault($this);
        return $version->getNamespace($this);
    }

    /**
     * Returns the default version for this Pain type.
     */
    public function getDefaultVersion(): PainVersion {
        return PainVersion::getDefault($this);
    }

    /**
     * Returns all supported versions for this Pain type.
     */
    public function getSupportedVersions(): array {
        return PainVersion::getSupportedVersions($this);
    }

    /**
     * Returns the German description text.
     */
    public function description(): string {
        return match ($this) {
            self::PAIN_001 => 'Überweisungsauftrag',
            self::PAIN_002 => 'Zahlungsstatusbericht',
            self::PAIN_007 => 'Rückrufauftrag',
            self::PAIN_008 => 'Lastschriftauftrag',
            self::PAIN_009 => 'Mandatsanfrage',
            self::PAIN_010 => 'Mandatsänderung',
            self::PAIN_011 => 'Mandatskündigung',
            self::PAIN_012 => 'Mandatsakzeptanz',
            self::PAIN_013 => 'Creditor-initiierte Zahlungsanfrage',
            self::PAIN_014 => 'Statusbericht Zahlungsanfrage',
            self::PAIN_017 => 'Mandatskopie-Anfrage',
            self::PAIN_018 => 'Mandatssuspendierung',
        };
    }

    /**
     * Returns the ISO 20022 message name (English).
     */
    public function getMessageName(): string {
        return match ($this) {
            self::PAIN_001 => 'Customer Credit Transfer Initiation',
            self::PAIN_002 => 'Customer Payment Status Report',
            self::PAIN_007 => 'Customer Payment Reversal',
            self::PAIN_008 => 'Customer Direct Debit Initiation',
            self::PAIN_009 => 'Mandate Initiation Request',
            self::PAIN_010 => 'Mandate Amendment Request',
            self::PAIN_011 => 'Mandate Cancellation Request',
            self::PAIN_012 => 'Mandate Acceptance Report',
            self::PAIN_013 => 'Creditor Payment Activation Request',
            self::PAIN_014 => 'Creditor Payment Activation Request Status Report',
            self::PAIN_017 => 'Mandate Copy Request',
            self::PAIN_018 => 'Mandate Suspension Request',
        };
    }

    /**
     * Returns the root element.
     */
    public function rootElement(): string {
        return match ($this) {
            self::PAIN_001 => 'CstmrCdtTrfInitn',
            self::PAIN_002 => 'CstmrPmtStsRpt',
            self::PAIN_007 => 'CstmrPmtRvsl',
            self::PAIN_008 => 'CstmrDrctDbtInitn',
            self::PAIN_009 => 'MndtInitnReq',
            self::PAIN_010 => 'MndtAmdmntReq',
            self::PAIN_011 => 'MndtCxlReq',
            self::PAIN_012 => 'MndtAccptncRpt',
            self::PAIN_013 => 'CdtrPmtActvtnReq',
            self::PAIN_014 => 'CdtrPmtActvtnReqStsRpt',
            self::PAIN_017 => 'MndtCpyReq',
            self::PAIN_018 => 'MndtSspnsnReq',
        };
    }

    /**
     * Erstellt PainType aus XML-Namespace.
     */
    public static function fromNamespace(string $namespace): ?self {
        foreach (self::cases() as $case) {
            if (str_contains($namespace, $case->value)) {
                return $case;
            }
        }
        return null;
    }

    /**
     * Erstellt PainType aus Root-Element.
     */
    public static function fromRootElement(string $element): ?self {
        foreach (self::cases() as $case) {
            if ($case->rootElement() === $element) {
                return $case;
            }
        }
        return null;
    }

    /**
     * Checks if this is a transfer format.
     */
    public function isCreditTransfer(): bool {
        return $this === self::PAIN_001;
    }

    /**
     * Checks if this is a direct debit format.
     */
    public function isDirectDebit(): bool {
        return $this === self::PAIN_008;
    }

    /**
     * Checks if this is a mandate format.
     */
    public function isMandate(): bool {
        return in_array($this, [
            self::PAIN_009,
            self::PAIN_010,
            self::PAIN_011,
            self::PAIN_012,
            self::PAIN_017,
            self::PAIN_018,
        ]);
    }

    /**
     * Checks if this is a status report.
     */
    public function isStatusReport(): bool {
        return in_array($this, [self::PAIN_002, self::PAIN_014]);
    }

    /**
     * Ermittelt den Pain-Typ aus einem XML-Dokument.
     * 
     * Uses regex to match xmlns namespace declarations to avoid false matches
     * on elements like OrgnlMsgNmId that might contain different message types.
     */
    public static function fromXml(string $xmlContent): ?self {
        // Match xmlns declarations: xmlns="urn:iso:std:iso:20022:tech:xsd:pain.001.001.12"
        // or xmlns:xxx="urn:iso:std:iso:20022:tech:xsd:pain.001.001.12"
        if (preg_match('/xmlns[^=]*=\s*["\']urn:iso:std:iso:20022:tech:xsd:(pain\.\d{3})\.001\.\d{2}["\']/', $xmlContent, $matches)) {
            $painTypeString = $matches[1];
            foreach (self::cases() as $case) {
                if ($case->value === $painTypeString) {
                    return $case;
                }
            }
        }
        return null;
    }
}
