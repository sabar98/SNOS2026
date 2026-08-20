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

class ReviewerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Reviewers', [
            'reviewers' => User::role('reviewer')
                ->withCount('reviewAssignments')
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/ReviewerForm');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $reviewer = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $reviewer->assignRole('reviewer');

        return redirect()->route('admin.reviewers.index')->with('status', 'reviewer-created');
    }

    public function edit(User $reviewer): Response
    {
        abort_unless($reviewer->hasRole('reviewer'), 404);

        return Inertia::render('Admin/ReviewerForm', [
            'reviewer' => $reviewer->only(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request, User $reviewer): RedirectResponse
    {
        abort_unless($reviewer->hasRole('reviewer'), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($reviewer->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $reviewer->name = $validated['name'];
        $reviewer->email = $validated['email'];

        if (! empty($validated['password'])) {
            $reviewer->password = Hash::make($validated['password']);
        }

        $reviewer->save();

        return redirect()->route('admin.reviewers.index')->with('status', 'reviewer-updated');
    }

    public function destroy(User $reviewer): RedirectResponse
    {
        abort_unless($reviewer->hasRole('reviewer'), 404);

        $reviewer->delete();

        return redirect()->route('admin.reviewers.index')->with('status', 'reviewer-deleted');
    }
}
