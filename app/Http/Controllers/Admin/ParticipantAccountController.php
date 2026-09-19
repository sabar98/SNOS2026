<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ParticipantAccountExporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParticipantAccountController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/ParticipantAccounts', [
            'participants' => User::role('peserta')
                ->withCount('eventRegistrations')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'nik', 'institution', 'whatsapp_number']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/ParticipantAccountForm');
    }

    public function export(ParticipantAccountExporter $exporter): StreamedResponse
    {
        $participants = User::role('peserta')
            ->withCount('eventRegistrations')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'nik', 'institution', 'whatsapp_number']);

        $filename = 'akun-peserta-'.now()->format('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($exporter, $participants) {
            $exporter->writeTo($participants)->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:30', 'unique:users,nik'],
            'institution' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $participant = User::create([
            'name' => $validated['name'],
            'nik' => $validated['nik'],
            'institution' => $validated['institution'],
            'whatsapp_number' => $validated['whatsapp_number'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $participant->assignRole('peserta');

        return redirect()->route('admin.participant-accounts.index')->with('status', 'participant-account-created');
    }

    public function edit(User $participantAccount): Response
    {
        abort_unless($participantAccount->hasRole('peserta'), 404);

        return Inertia::render('Admin/ParticipantAccountForm', [
            'participant' => $participantAccount->only(['id', 'name', 'email', 'nik', 'institution', 'whatsapp_number']),
        ]);
    }

    public function update(Request $request, User $participantAccount): RedirectResponse
    {
        abort_unless($participantAccount->hasRole('peserta'), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:30', Rule::unique('users', 'nik')->ignore($participantAccount->id)],
            'institution' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($participantAccount->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $participantAccount->name = $validated['name'];
        $participantAccount->nik = $validated['nik'];
        $participantAccount->institution = $validated['institution'];
        $participantAccount->whatsapp_number = $validated['whatsapp_number'];
        $participantAccount->email = $validated['email'];

        if (! empty($validated['password'])) {
            $participantAccount->password = Hash::make($validated['password']);
        }

        $participantAccount->save();

        return redirect()->route('admin.participant-accounts.index')->with('status', 'participant-account-updated');
    }

    public function destroy(User $participantAccount): RedirectResponse
    {
        abort_unless($participantAccount->hasRole('peserta'), 404);

        $participantAccount->delete();

        return redirect()->route('admin.participant-accounts.index')->with('status', 'participant-account-deleted');
    }
}
