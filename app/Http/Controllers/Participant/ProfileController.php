<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        $user = Auth::user();

        return Inertia::render('Participant/ProfileEdit', [
            'profile' => $user->profile,
            'whatsappNumber' => $user->whatsapp_number,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title_prefix' => ['nullable', 'string', 'max:50'],
            'title_suffix' => ['nullable', 'string', 'max:50'],
            'gender' => ['required', 'in:laki_laki,perempuan'],
            'address' => ['required', 'string'],
            'institution' => ['required', 'string', 'max:255'],
            'study_program' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'student_card' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $user->update(['whatsapp_number' => $validated['whatsapp_number']]);

        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);
        $profile->fill([
            'title_prefix' => $validated['title_prefix'] ?? null,
            'title_suffix' => $validated['title_suffix'] ?? null,
            'gender' => $validated['gender'],
            'address' => $validated['address'],
            'institution' => $validated['institution'],
            'study_program' => $validated['study_program'],
        ]);

        if ($request->hasFile('avatar')) {
            $profile->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->hasFile('student_card')) {
            $profile->student_card_path = $request->file('student_card')->store('student-cards', 'public');
        }

        $profile->is_complete = true;
        $profile->save();

        return back()->with('status', 'profile-updated');
    }
}
