<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class ModeratorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Moderators', [
            'moderators' => User::role('moderator')
                ->withCount('moderatedSessions')
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/ModeratorForm');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $moderator = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $moderator->assignRole('moderator');

        return redirect()->route('admin.moderators.index')->with('status', 'moderator-created');
    }

    public function edit(User $moderator): Response
    {
        abort_unless($moderator->hasRole('moderator'), 404);

        return Inertia::render('Admin/ModeratorForm', [
            'moderator' => $moderator->only(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request, User $moderator): RedirectResponse
    {
        abort_unless($moderator->hasRole('moderator'), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($moderator->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $moderator->name = $validated['name'];
        $moderator->email = $validated['email'];

        if (! empty($validated['password'])) {
            $moderator->password = Hash::make($validated['password']);
        }

        $moderator->save();

        return redirect()->route('admin.moderators.index')->with('status', 'moderator-updated');
    }

    public function destroy(User $moderator): RedirectResponse
    {
        abort_unless($moderator->hasRole('moderator'), 404);

        $moderator->delete();

        return redirect()->route('admin.moderators.index')->with('status', 'moderator-deleted');
    }
}
