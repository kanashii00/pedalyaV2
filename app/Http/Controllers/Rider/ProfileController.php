<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Services\DocumentUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected DocumentUploadService $documentUploadService
    ) {}

    public function show(Request $request): View
    {
        $user = $request->user();

        $totalRentals = $user->totalRentals;
        $totalSpent = $user->totalSpent;

        return view('rider.profile', compact('user', 'totalRentals', 'totalSpent'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'displayName' => ['sometimes', 'string', 'max:255'],
            'phoneNumber' => ['sometimes', 'string', 'max:20'],
            'address' => ['sometimes', 'string', 'max:500'],
        ]);

        $fields = [];

        if (isset($validated['displayName'])) {
            $fields['name'] = $validated['displayName'];
        }
        if (isset($validated['phoneNumber'])) {
            $fields['phoneNumber'] = $validated['phoneNumber'];
        }
        if (isset($validated['address'])) {
            $fields['address'] = $validated['address'];
        }

        if (!empty($fields)) {
            $user->update($fields);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update(['password' => $validated['password']]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->rentals()->where('status', 'active')->exists()) {
            return back()->withErrors(['account' => 'You cannot delete an account with an active rental.']);
        }

        \Illuminate\Support\Facades\Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account has been deleted.');
    }

    public function uploadId(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'id_image' => ['required_without:id_image_base64', 'image', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
            'id_image_base64' => ['required_without:id_image', 'string'],
        ]);

        $stored = $this->documentUploadService
            ->storeIdVerification($request, $user->id);

        if ($stored === null) {
            return back()->withErrors(['id_image' => 'The provided document is not a valid JPEG, PNG or PDF file.']);
        }

        $idVerification = $user->idVerification ?? [];
        $idVerification['id_path'] = $stored['id_path'];
        $idVerification['id_url'] = $stored['id_url'];
        $idVerification['status'] = 'pending';
        $idVerification['submitted_at'] = now()->toIso8601String();

        $user->update(['idVerification' => $idVerification, 'idUploaded' => true]);

        return back()->with('success', 'ID submitted for verification.');
    }
}
