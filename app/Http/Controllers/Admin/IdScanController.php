<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdScan;
use App\Models\User;
use App\Services\IdScanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IdScanController extends Controller
{
    public function __construct(
        protected IdScanService $idScanService
    ) {}

    public function index(Request $request): Response
    {
        $query = IdScan::query()->with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('documentType')) {
            $query->where('documentType', $request->input('documentType'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $hash = $this->idScanService->hashIdNumber($search);

            $query->where(function ($q) use ($search, $hash) {
                $q->when($hash, fn ($w) => $w->orWhere('idNumberHash', $hash))
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phoneNumber', 'like', "%{$search}%"));
            });
        }

        $scans = $query->latest()->paginate(15);

        return response()->view('admin.idscans', compact('scans'));
    }

    public function create(Request $request): Response
    {
        $riders = User::where('role', 'rider')
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name', 'email', 'phoneNumber', 'verified']);

        return response()->view('admin.idscans.scanner', compact('riders'));
    }

    public function show(int $id): Response
    {
        $scan = IdScan::with('user', 'reviewer')->findOrFail($id);

        return response()->view('admin.idscans.show', compact('scan'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'documentType' => ['required', 'string', 'max:50'],
            'userId' => ['nullable', 'exists:users,id'],
            'fullName' => ['nullable', 'string', 'max:255'],
            'idNumber' => ['nullable', 'string', 'max:100'],
            'dateOfBirth' => ['nullable', 'string', 'max:50'],
            'expirationDate' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'rawOcrText' => ['nullable', 'string'],
            'ocrConfidence' => ['nullable', 'numeric', 'between:0,100'],
            'qualityScore' => ['nullable', 'numeric', 'between:0,100'],
            'blurScore' => ['nullable', 'numeric', 'between:0,100'],
            'glareScore' => ['nullable', 'numeric', 'between:0,100'],
            'frontImage' => ['required_without:frontImagePath', 'nullable', 'string'],
            'backImage' => ['nullable', 'string'],
        ]);

        $scan = $this->idScanService->createFromScan($validated, auth()->id());

        if ($scan->idNumber) {
            $duplicate = $this->idScanService->findDuplicate($scan->idNumber);
            if ($duplicate) {
                $scan->update(['status' => IdScan::STATUS_REVIEW]);

                return redirect()->route('admin.id-scans.show', $scan->id)
                    ->with('warning', 'A matching ID number was found. Review the duplicate scan before approval.');
            }

            $returning = $this->idScanService->findReturningRenter($scan->idNumber);
            if ($returning) {
                $scan->update(['status' => IdScan::STATUS_REVIEW, 'userId' => $returning->id]);

                return redirect()->route('admin.id-scans.show', $scan->id)
                    ->with('success', 'Returning renter recognized. Scan linked to ' . $returning->name . ' for review.');
            }
        }

        return redirect()->route('admin.id-scans.show', $scan->id)
            ->with('success', 'ID scanned successfully. Review before approval.');
    }

    public function review(Request $request, int $id): RedirectResponse
    {
        $scan = IdScan::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:approved,rejected'],
            'reviewNotes' => ['nullable', 'string', 'max:1000'],
            'rejectionReason' => ['nullable', 'string', 'max:500'],
            'editFields' => ['nullable', 'boolean'],
            'fullName' => ['nullable', 'string', 'max:255'],
            'idNumber' => ['nullable', 'string', 'max:100'],
            'dateOfBirth' => ['nullable', 'string', 'max:50'],
            'expirationDate' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'userId' => ['nullable', 'exists:users,id'],
        ]);

        if (isset($validated['userId'])) {
            $scan->update(['userId' => $validated['userId']]);
        }

        $this->idScanService->review($scan, $validated, auth()->id());

        return redirect()->route('admin.id-scans.index')
            ->with('success', 'ID scan ' . $validated['status'] . '.');
    }

    public function serveImage(int $id, string $side): Response
    {
        $scan = IdScan::findOrFail($id);

        [$binary, $mime] = $this->idScanService->serveImage($scan, $side);

        if (!$binary) {
            abort(404, 'Image not found.');
        }

        return response($binary, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'no-store, private, max-age=0',
        ]);
    }
}
