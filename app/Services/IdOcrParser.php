<?php

namespace App\Services;

class IdOcrParser
{
    public function parse(string $rawText, ?string $documentType = null): array
    {
        $lines = $this->normalizeLines($rawText);
        $fullText = mb_strtoupper(implode("\n", $lines));

        $documentType = $documentType ?: $this->detectDocumentType($fullText, $lines);

        return [
            'fullName' => $this->extractName($lines, $documentType),
            'idNumber' => $this->extractIdNumber($lines, $documentType),
            'dateOfBirth' => $this->extractDateOfBirth($lines),
            'expirationDate' => $this->extractExpirationDate($lines),
            'address' => $this->extractAddress($lines),
            'documentType' => $documentType,
        ];
    }

    public function detectDocumentType(string $fullText, array $lines): string
    {
        $t = $fullText;

        if (preg_match('/PHILSYS|PHILIPPINE IDENTIFICATION|PSSN|PSN\b|PHILIPPINE NATIONAL ID/', $t)) {
            return 'national_id';
        }
        if (preg_match('/DRIVER.?S LICENSE|DRIVING LICENSE|REPUBLIC OF THE PHILIPPINES[^\n]*LICENSE/', $t)) {
            return 'drivers_license';
        }
        if (preg_match('/PASSPORT|REPUBLIC OF THE PHILIPPINES[\s\S]*PASSPORT|UNITED KINGDOM[\s\S]*PASSPORT/', $t)) {
            return 'passport';
        }
        if (preg_match('/UMID|UNIFIED MULTI-PURPOSE ID/', $t)) {
            return 'umid';
        }
        if (preg_match('/PHILHEALTH|PHILHEALTH ID/', $t)) {
            return 'philhealth_id';
        }
        if (preg_match('/VOTER|COMMISSION ON ELECTIONS/', $t)) {
            return 'voters_id';
        }
        if (preg_match('/STUDENT ID|UNIVERSITY|COLLEGE|SCHOOL|STUDENT NUMBER/', $t)) {
            return 'student_id';
        }

        return 'other';
    }

    protected function normalizeLines(string $rawText): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $rawText);
        $lines = array_map('trim', $lines);
        $lines = array_values(array_filter($lines, fn ($l) => $l !== ''));

        return $lines;
    }

    protected function extractName(array $lines, string $type): ?string
    {
        $markers = [
            '/NAME OF APPLICANT/i', '/FULL NAME/i', '/NAME OF HOLDER/i',
            '/SURNAME\s*,\s*GIVEN/i', '/LEGAL NAME/i', '/NAME\s*:/i',
        ];

        foreach ($markers as $marker) {
            foreach ($lines as $i => $line) {
                if (preg_match($marker, $line)) {
                    // Value on the same line after a colon/semicolon, or on the next line
                    $value = preg_replace($marker, '', $line);
                    $value = trim($value, " \t:;—-|");
                    if ($value !== '' && !$this->looksLikeLabel($value)) {
                        return $this->titleCase($value);
                    }
                    if (isset($lines[$i + 1])) {
                        $next = $this->stripFieldValue($lines[$i + 1]);
                        if ($next !== '' && !$this->isDate($next) && !$this->isMostlyDigits($next)) {
                            return $this->titleCase($next);
                        }
                    }
                }
            }
        }

        // Fallback: pick the longest all-caps "name-like" line that is not a label/date/address
        $best = null;
        $bestScore = 0;
        foreach ($lines as $line) {
            $clean = preg_replace('/[^A-Z\s\.,\'-]/u', '', $line);
            $words = preg_split('/\s+/', trim($clean));
            $letters = preg_replace('/[^A-Z]/', '', $line);
            if (count($words) >= 2 && count($words) <= 5 && strlen($letters) >= 6 && !$this->isDate($line)) {
                $score = strlen($letters) - (str_contains($line, 'PHILIPPINES') || str_contains($line, 'REPUBLIC') || str_contains($line, 'IDENTIFICATION') || str_contains($line, 'GOVERNMENT') ? 40 : 0);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $best = $clean;
                }
            }
        }

        if ($best !== null) {
            return $this->titleCase($best);
        }

        // Last-resort: the line right before the signature/mr. — too unreliable; return null
        return null;
    }

    protected function extractIdNumber(array $lines, string $type): ?string
    {
        // Look for a line/label pairing first, then type-specific patterns, then long digit runs
        foreach ($lines as $i => $line) {
            if (preg_match('/(ID NO|ID NUMBER|ID #|NO\.|NUMBER|REF(?:ERENCE)? NO|DL NUMBER|LICENSE NO|LTO|CRN|MDR|PSN|PSSN|PASSPORT NO)/i', $line) && preg_match('/([A-Z0-9][A-Z0-9\s\-\.]{6,})/', $line, $m)) {
                $candidate = trim($m[1], " \t:;—-.");
                if (strlen(preg_replace('/[^A-Z0-9]/', '', $candidate)) >= 6) {
                    return $candidate;
                }
            }
        }

        $joined = implode("\n", $lines);

        $patterns = match ($type) {
            'drivers_license' => [
                '/\b\d{1,2}[-_]\d{2}[-_]\d{1,2}[-_]\d{3,6}\b/',
                '/\b[A-Z]{1,2}\d{2}[-_]\d{2}[-_]\d{3,6}\b/',
                '/\bD\d{1,2}[-_]\d{2}[-_]\d{6}\b/',
                '/\b\d{2}[-_]\d{2}[-_]\d{6}\b/',
            ],
            'passport' => [
                '/\b[A-Z][A-Z0-9]{8}\b/',
                '/\b[A-Z]\d{7}[A-Z0-9]?\b/',
            ],
            'national_id' => [
                '/\b\d{4}[-_ ]?\d{4}[-_ ]?\d{4}\b/',
                '/\b\d{12}\b/',
            ],
            'umid' => [
                '/\b\d{4}[-_ ]?\d{4}[-_ ]?\d{4}\b/',
                '/\b\d{12}\b/',
                '/\b\d{6}[-_ ]?\d{6}\b/',
            ],
            'philhealth_id' => [
                '/\b\d{2}-\d{8}-\d{1}\b/',
                '/\b\d{11}\b/',
            ],
            'student_id' => [
                '/\b\d{3,4}[-_ ]?\d{3,5}\b/',
                '/\b\d{6,9}\b/',
            ],
            default => [
                '/\b\d{3}[-_ ]?\d{3}[-_ ]?\d{4}\b/',
                '/\b\d{10,12}\b/',
            ],
        };

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $joined, $m)) {
                $id = preg_replace('/\s+/', '', $m[0]);
                return $id;
            }
        }

        // Generic fallback: any 8+ digit run
        if (preg_match('/\b\d{8,}\b/', $joined, $m)) {
            return $m[0];
        }

        return null;
    }

    protected function extractDateOfBirth(array $lines): ?string
    {
        $datePattern = '/\b(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})\b/';
        $dateValues = [];

        foreach ($lines as $i => $line) {
            $isBirthLabel = preg_match('/(BIRTH|B\.O\.|B\/O|BORN|BDAY|DATE OF BIRTH|DOB)/i', $line);

            if ($isBirthLabel) {
                foreach ($lines as $other) {
                    if (preg_match($datePattern, $other, $m)) {
                        return $this->normalizeDate($m[0]);
                    }
                }
            }

            if (preg_match($datePattern, $line, $m)) {
                $dateValues[] = $line;
            }
        }

        // Prefer a date on a line that also contains "BIRTH" or a short date
        foreach ($dateValues as $line) {
            if (preg_match($datePattern, $line, $m)) {
                $d = $this->normalizeDate($m[0]);
                if ($this->plausibleBirthYear($m[3])) {
                    return $d;
                }
            }
        }

        // Last resort: return first 19xx/20xx date
        foreach ($dateValues as $line) {
            if (preg_match($datePattern, $line, $m) && $this->plausibleBirthYear($m[3])) {
                return $this->normalizeDate($m[0]);
            }
        }

        return null;
    }

    protected function extractExpirationDate(array $lines): ?string
    {
        $datePattern = '/\b(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})\b/';

        foreach ($lines as $line) {
            if (preg_match('/(EXPIR|EXPIRY|EXPIRED|VALID UNTIL|VALID THRU|DATE OF EXP|EXPIRATION|NOT VALID AFTER)/i', $line)) {
                foreach ($lines as $other) {
                    if (preg_match($datePattern, $other, $m)) {
                        $y = (int) $m[3];
                        if (strlen($m[3]) === 2) {
                            $y = $m[3] >= 40 ? 1900 + (int) $m[3] : 2000 + (int) $m[3];
                        }
                        if ($y >= 2020 && $y <= (int) date('Y') + 20) {
                            return $this->normalizeDate($m[0]);
                        }
                    }
                }
            }
        }

        foreach ($lines as $line) {
            if (preg_match($datePattern, $line, $m)) {
                $y = (int) $m[3];
                if (strlen($m[3]) === 2) {
                    $y = $m[3] >= 40 ? 1900 + (int) $m[3] : 2000 + (int) $m[3];
                }
                if ($y >= (int) date('Y') && $y <= (int) date('Y') + 20) {
                    return $this->normalizeDate($m[0]);
                }
            }
        }

        return null;
    }

    protected function extractAddress(array $lines): ?string
    {
        $addressParts = [];

        foreach ($lines as $i => $line) {
            if (preg_match('/(ADDRESS|RESIDENCE|HOME ADDRESS|PERMANENT ADDRESS|CURRENT ADDRESS|BARANGAY|MUNICIPALITY|CITY)/i', $line)) {
                $value = preg_replace('/^[^:]{0,25}[:—-]/i', '', $line);
                $value = trim($value, " \t:;—-|");
                if (strlen($value) < 4 || $this->isLabelOnly($value)) {
                    $value = '';
                }
                if ($value !== '' && !$this->looksLikeLabel($value)) {
                    $addressParts[] = $value;
                }
                for ($j = 1; $j <= 3 && isset($lines[$i + $j]); $j++) {
                    $candidate = $this->stripFieldValue($lines[$i + $j]);
                    if ($candidate === '' || $this->isDate($candidate) || $this->isMostlyDigits($candidate)
                        || $this->looksLikeLabel($candidate) || $this->isLabelOnly($candidate)
                        || preg_match('/(ID NO|ID NUMBER|NUMBER|NO\.|EXPIR|VALID UNTIL|SEX|MARITAL|CITIZEN|NATIONALITY|BLOOD|HEIGHT|WEIGHT|SIGNATURE|DATE|PSSN|PSN\b|CRN|LTO|REF|PHILSYS|UMID|PASSPORT)/i', $candidate)) {
                        break;
                    }
                    $addressParts[] = $candidate;
                }
                break;
            }
        }

        if (!empty($addressParts)) {
            return $this->titleCase(implode(', ', array_slice($addressParts, 0, 3)));
        }

        // Fallback: concatenate lines containing typical PH address keywords
        $found = [];
        foreach ($lines as $line) {
            if (preg_match('/(STREET|BRGY|BARANGAY|MUNICIPALITY|CITY|PROVINCE|ZONE|SITIO|PUROK|ROAD|AVE)/i', $line) && !preg_match('/(PHILIPPINES|IDENTIFICATION|REPUBLIC|GOVERNMENT|SEX|MARITAL|CITIZEN)/i', $line)) {
                $found[] = $this->stripFieldValue($line);
            }
        }
        $found = array_values(array_filter($found, fn ($f) => strlen($f) > 3));

        return $found ? $this->titleCase(implode(', ', array_slice($found, 0, 3))) : null;
    }

    protected function isLabelOnly(string $value): bool
    {
        return (bool) preg_match('/^(ADDRESS|RESIDENCE|PERMANENT ADDRESS|CURRENT ADDRESS|HOME ADDRESS|BARANGAY|MUNICIPALITY|CITY|NAME|FULL NAME|DATE OF BIRTH|BIRTH)$/i', trim($value));
    }

    protected function stripFieldValue(string $line): string
    {
        return trim(preg_replace('/^[^:]{0,30}[:—-]/i', '', $line), " \t:;—-|");
    }

    protected function looksLikeLabel(string $value): bool
    {
        return preg_match('/^[A-Z\s]{4,}[:—-]$/i', $value) || preg_match('/^(REPUBLIC|PHILIPPINES|GOVERNMENT|IDENTIFICATION|MOTOR|TRANSPORT|DEPARTMENT|DATE|SIGNATURE|MR\.|MRS\.|MS\.|NONE)$/i', trim($value));
    }

    protected function isDate(string $value): bool
    {
        return (bool) preg_match('/\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}/', $value);
    }

    protected function isMostlyDigits(string $value): bool
    {
        $digits = strlen(preg_replace('/[^0-9]/', '', $value));
        return $digits > 0 && $digits / max(strlen($value), 1) > 0.5;
    }

    protected function plausibleBirthYear(string $year): bool
    {
        $y = (int) $year;
        if (strlen($year) === 2) {
            return $y >= 40 && $y <= 99 || $y >= 0 && $y <= 20;
        }
        return $y >= 1920 && $y <= (int) date('Y') - 10;
    }

    protected function normalizeDate(string $date): string
    {
        if (preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})/', $date, $m)) {
            $year = strlen($m[3]) === 2 ? ($m[3] >= 40 ? '19'.$m[3] : '20'.$m[3]) : $m[3];
            return str_pad($m[1], 2, '0', STR_PAD_LEFT).'-'.str_pad($m[2], 2, '0', STR_PAD_LEFT).'-'.$year;
        }
        return $date;
    }

    protected function titleCase(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/', ' ', $value);
        return preg_replace_callback('/(^|[\.\s\-])[a-z]/', fn ($m) => mb_strtoupper($m[0]), $value);
    }
}
