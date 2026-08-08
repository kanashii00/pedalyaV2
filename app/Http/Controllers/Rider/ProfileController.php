<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
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

        $idVerification = $user->idVerification ?? [];

        if ($request->hasFile('id_image')) {
            $path = $request->file('id_image')->store('id-verifications', 'public');
            $idVerification['id_path'] = $path;
            $idVerification['id_url'] = Storage::disk('public')->url($path);
        } elseif ($request->filled('id_image_base64')) {
            $base64 = $request->input('id_image_base64');
            $data = explode(',', $base64);
            $mime = 'image/png';
            if (str_contains($base64, 'data:image/jpeg')) {
                $mime = 'image/jpeg';
            } elseif (str_contains($base64, 'data:application/pdf')) {
                $mime = 'application/pdf';
            }

            $extension = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'application/pdf' => 'pdf',
                default => 'png',
            };

            $filename = 'id-' . $user->id . '-' . time() . '.' . $extension;
            $binary = base64_decode(end($data));
            $path = 'id-verifications/' . $filename;

            Storage::disk('public')->put($path, $binary);

            $idVerification['id_path'] = $path;
            $idVerification['id_url'] = Storage::disk('public')->url($path);
        }

        $idVerification['status'] = 'pending';
        $idVerification['submitted_at'] = now()->toIso8601String();

        $user->update(['idVerification' => $idVerification, 'idUploaded' => true]);

        return back()->with('success', 'ID submitted for verification.');
    }
}
