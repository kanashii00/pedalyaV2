<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentUploadService
{
    public function storeIdVerification(Request $request, int $userId): ?array
    {
        if ($request->hasFile('id_image')) {
            return $this->storeUploadedFile($request->file('id_image'));
        }

        if (!$request->filled('id_image_base64')) {
            return null;
        }

        return $this->storeDecoded(
            $this->decodeBase64DataUri(
                (string) $request->input('id_image_base64')
            ),
            $userId
        );
    }

    private function storeUploadedFile(UploadedFile $file): array
    {
        $path = $file->store('id-verifications', 'public');

        return [
            'id_path' => $path,
            'id_url' => Storage::disk('public')->url($path),
        ];
    }

    private function storeDecoded(?array $decoded, int $userId): ?array
    {
        if ($decoded === null) {
            return null;
        }

        [$mime, $binary] = $decoded;

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'application/pdf' => 'pdf',
            default => 'png',
        };

        $filename = 'id-' . $userId . '-' . time() . '.' . $extension;
        $path = 'id-verifications/' . $filename;

        Storage::disk('public')->put($path, $binary);

        return [
            'id_path' => $path,
            'id_url' => Storage::disk('public')->url($path),
        ];
    }

    private function decodeBase64DataUri(string $dataUri): ?array
    {
        $data = explode(',', $dataUri, 2);
        $encoded = end($data);

        if ($encoded === false || $encoded === '') {
            return null;
        }

        $binary = base64_decode((string) $encoded, true);

        if ($binary === false || strlen($binary) === 0 || strlen($binary) > 5 * 1024 * 1024) {
            return null;
        }

        $mime = $this->detectMimeType($binary);

        return $mime === null ? null : [$mime, $binary];
    }

    private function detectMimeType(string $binary): ?string
    {
        $info = @getimagesizefromstring($binary);

        if ($info !== false && in_array($info['mime'], ['image/jpeg', 'image/png'], true)) {
            return $info['mime'];
        }

        if (substr($binary, 0, 5) === '%PDF-') {
            return 'application/pdf';
        }

        return null;
    }
}
