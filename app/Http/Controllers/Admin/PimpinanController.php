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

class PimpinanController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Pimpinan', [
            'pimpinan' => User::role('pimpinan')
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/PimpinanForm');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $leader = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $leader->assignRole('pimpinan');

        return redirect()->route('admin.pimpinan.index')->with('status', 'pimpinan-created');
    }

    public function edit(User $pimpinan): Response
    {
        abort_unless($pimpinan->hasRole('pimpinan'), 404);

        return Inertia::render('Admin/PimpinanForm', [
            'pimpinan' => $pimpinan->only(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request, User $pimpinan): RedirectResponse
    {
        abort_unless($pimpinan->hasRole('pimpinan'), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($pimpinan->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $pimpinan->name = $validated['name'];
        $pimpinan->email = $validated['email'];

        if (! empty($validated['password'])) {
            $pimpinan->password = Hash::make($validated['password']);
        }

        $pimpinan->save();

        return redirect()->route('admin.pimpinan.index')->with('status', 'pimpinan-updated');
    }

    public function destroy(User $pimpinan): RedirectResponse
    {
        abort_unless($pimpinan->hasRole('pimpinan'), 404);

        $pimpinan->delete();

        return redirect()->route('admin.pimpinan.index')->with('status', 'pimpinan-deleted');
    }
}
