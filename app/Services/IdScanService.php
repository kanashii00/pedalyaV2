<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\IdScan;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IdScanService
{
    public function __construct(protected IdOcrParser $parser)
    {
    }

    public function hashIdNumber(?string $idNumber): ?string
    {
        if (!$idNumber) {
            return null;
        }

        return hash('sha256', strtoupper(preg_replace('/[^A-Z0-9]/', '', $idNumber)));
    }

    public function findDuplicate(string $idNumber, array $statuses = [IdScan::STATUS_APPROVED]): ?IdScan
    {
        $hash = $this->hashIdNumber($idNumber);

        if (!$hash) {
            return null;
        }

        return IdScan::where('idNumberHash', $hash)
            ->when($statuses, fn ($q) => $q->whereIn('status', $statuses))
            ->latest()
            ->first();
    }

    public function findReturningRenter(string $idNumber): ?User
    {
        $hash = $this->hashIdNumber($idNumber);

        if (!$hash) {
            return null;
        }

        $approved = IdScan::where('idNumberHash', $hash)
            ->where('status', IdScan::STATUS_APPROVED)
            ->whereNotNull('userId')
            ->latest()
            ->first();

        return $approved?->user;
    }

    public function storeImage(string $base64Data): array
    {
        $data = explode(',', $base64Data, 2);
        $mime = 'image/png';
        if (str_contains($base64Data, 'data:image/jpeg')) {
            $mime = 'image/jpeg';
        } elseif (str_contains($base64Data, 'data:image/webp')) {
            $mime = 'image/webp';
        }

        $binary = base64_decode(end($data));
        $encrypted = Crypt::encryptString($binary);

        $path = 'id-scans/' . Str::random(40) . '.enc';

        Storage::disk('local')->put($path, $encrypted);

        return [
            'path' => $path,
            'mime' => $mime,
        ];
    }

    public function serveImage(IdScan $scan, string $side): array
    {
        $path = $side === 'back' ? $scan->backImagePath : $scan->frontImagePath;
        $mime = $side === 'back' ? $scan->backImageMime : $scan->frontImageMime;

        if (!$path || !Storage::disk('local')->exists($path)) {
            return [null, null];
        }

        $binary = Crypt::decryptString(Storage::disk('local')->get($path));

        return [$binary, $mime ?? 'image/jpeg'];
    }

    public function parseOcrText(?string $rawOcrText, ?string $documentType = null): array
    {
        if (!$rawOcrText) {
            return [];
        }

        return $this->parser->parse($rawOcrText, $documentType);
    }

    public function createFromScan(array $data, int $adminId): IdScan
    {
        $front = $data['frontImage'] ?? null;
        $back = $data['backImage'] ?? null;

        $extracted = $this->parseOcrText($data['rawOcrText'] ?? null, $data['documentType'] ?? null);

        $fullName = $data['fullName'] ?? $extracted['fullName'] ?? null;
        $idNumber = $data['idNumber'] ?? $extracted['idNumber'] ?? null;

        $scanData = [
            'userId' => $data['userId'] ?? null,
            'documentType' => $data['documentType'] ?? $extracted['documentType'] ?? 'other',
            'idNumberHash' => $this->hashIdNumber($idNumber),
            'fullName' => $fullName,
            'idNumber' => $idNumber,
            'dateOfBirth' => $data['dateOfBirth'] ?? $extracted['dateOfBirth'] ?? null,
            'expirationDate' => $data['expirationDate'] ?? $extracted['expirationDate'] ?? null,
            'address' => $data['address'] ?? $extracted['address'] ?? null,
            'extractedData' => $extracted,
            'rawOcrText' => $data['rawOcrText'] ?? null,
            'ocrConfidence' => $data['ocrConfidence'] ?? null,
            'qualityScore' => $data['qualityScore'] ?? null,
            'blurScore' => $data['blurScore'] ?? null,
            'glareScore' => $data['glareScore'] ?? null,
            'status' => IdScan::STATUS_PENDING,
        ];

        if ($front) {
            $storedFront = $this->storeImage($front);
            $scanData['frontImagePath'] = $storedFront['path'];
            $scanData['frontImageMime'] = $storedFront['mime'];
        }

        if ($back) {
            $storedBack = $this->storeImage($back);
            $scanData['backImagePath'] = $storedBack['path'];
            $scanData['backImageMime'] = $storedBack['mime'];
        }

        $scan = IdScan::create($scanData);

        AuditLog::record('id_scan_created', $adminId, [
            'idScanId' => $scan->id,
            'documentType' => $scanData['documentType'],
            'duplicate' => $idNumber ? (bool) $this->findDuplicate($idNumber) : false,
        ]);

        return $scan;
    }

    public function review(IdScan $scan, array $data, int $adminId): IdScan
    {
        $approved = $data['status'] === IdScan::STATUS_APPROVED;

        $updateData = [
            'status' => $data['status'],
            'reviewedBy' => $adminId,
            'reviewedAt' => now(),
            'reviewNotes' => $data['reviewNotes'] ?? null,
            'rejectionReason' => $approved ? null : ($data['rejectionReason'] ?? null),
        ];

        if (($data['editFields'] ?? false) === true) {
            $updateData['fullName'] = $data['fullName'] ?? $scan->fullName;
            $updateData['idNumber'] = $data['idNumber'] ?? $scan->idNumber;
            $updateData['dateOfBirth'] = $data['dateOfBirth'] ?? $scan->dateOfBirth;
            $updateData['expirationDate'] = $data['expirationDate'] ?? $scan->expirationDate;
            $updateData['address'] = $data['address'] ?? $scan->address;

            if ($updateData['idNumber']) {
                $updateData['idNumberHash'] = $this->hashIdNumber($updateData['idNumber']);
            }
        }

        if ($approved && $scan->userId) {
            $user = User::find($scan->userId);
            if ($user) {
                $user->update([
                    'verified' => true,
                    'idUploaded' => true,
                    'idVerification' => array_merge($user->idVerification ?? [], [
                        'approved' => true,
                        'reason' => 'Verified via automated ID scanner',
                        'idScanId' => $scan->id,
                        'verified_at' => now()->toISOString(),
                    ]),
                ]);
            }
        }

        $scan->update($updateData);

        AuditLog::record('id_scan_' . ($approved ? 'approved' : 'rejected'), $adminId, [
            'idScanId' => $scan->id,
            'reason' => $updateData['rejectionReason'],
        ]);

        return $scan;
    }
}
