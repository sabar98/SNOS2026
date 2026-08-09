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

class NarasumberController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Narasumber', [
            'narasumber' => User::role('narasumber')
                ->withCount(['certificates as certificates_count' => fn ($query) => $query->where('role', 'narasumber')])
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $speaker = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $speaker->assignRole('narasumber');

        return back()->with('status', 'narasumber-created');
    }

    public function update(Request $request, User $narasumber): RedirectResponse
    {
        abort_unless($narasumber->hasRole('narasumber'), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($narasumber->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $narasumber->name = $validated['name'];
        $narasumber->email = $validated['email'];

        if (! empty($validated['password'])) {
            $narasumber->password = Hash::make($validated['password']);
        }

        $narasumber->save();

        return back()->with('status', 'narasumber-updated');
    }

    public function destroy(User $narasumber): RedirectResponse
    {
        abort_unless($narasumber->hasRole('narasumber'), 404);

        $narasumber->delete();

        return back()->with('status', 'narasumber-deleted');
    }
}
