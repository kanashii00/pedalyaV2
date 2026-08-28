<?php

namespace Tests\Unit;

use App\Services\DocumentUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadServiceTest extends TestCase
{
    private DocumentUploadService $service;

    private const PNG_B64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9ZQmcAAAAASUVORK5CYII=';

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DocumentUploadService();
        Storage::fake('public');
    }

    public function test_store_with_uploaded_file(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'png');
        file_put_contents($tmp, base64_decode(self::PNG_B64));

        $request = Request::create('/api/auth/id-verification', 'POST', [], [], [
            'id_image' => new UploadedFile($tmp, 'id.png', 'image/png', null, true),
        ]);

        $result = $this->service->storeIdVerification($request, 7);

        $this->assertNotNull($result);
        $this->assertTrue(is_string($result['id_path']) && is_string($result['id_url']));
        Storage::disk('public')->assertExists($result['id_path']);

        @unlink($tmp);
    }

    public function test_store_with_base64_pdf(): void
    {
        $pdf = '%PDF-1.4 fake pdf content for id verification upload';
        $dataUri = 'data:application/pdf;base64,' . base64_encode($pdf);

        $request = Request::create('/api/auth/id-verification', 'POST');
        $request->merge(['id_image_base64' => $dataUri]);

        $result = $this->service->storeIdVerification($request, 8);

        $this->assertNotNull($result);
        $this->assertStringEndsWith('.pdf', $result['id_path']);
        Storage::disk('public')->assertExists($result['id_path']);
    }

    public function test_store_with_invalid_base64_returns_null(): void
    {
        $request = Request::create('/api/auth/id-verification', 'POST');
        $request->merge(['id_image_base64' => 'data:image/png;base64,not-base64!!']);

        $this->assertNull($this->service->storeIdVerification($request, 9));
    }

    public function test_store_without_file_or_base64_returns_null(): void
    {
        $request = Request::create('/api/auth/id-verification', 'POST');

        $this->assertNull($this->service->storeIdVerification($request, 10));
    }
}
