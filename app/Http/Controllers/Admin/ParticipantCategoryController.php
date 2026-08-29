<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventRegistration;
use App\Models\ParticipantCategory;
use App\Models\RegistrationFee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ParticipantCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/ParticipantCategories', [
            'participantCategories' => ParticipantCategory::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:participant_categories,key'],
            'label' => ['required', 'string', 'max:255'],
            'golongan' => ['required', Rule::in(ParticipantCategory::GOLONGAN_OPTIONS)],
            'is_presenter' => ['boolean'],
        ]);

        ParticipantCategory::create($validated + ['is_active' => true]);

        return back()->with('status', 'participant-category-created');
    }

    public function update(Request $request, ParticipantCategory $participantCategory): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'golongan' => ['required', Rule::in(ParticipantCategory::GOLONGAN_OPTIONS)],
            'is_presenter' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $participantCategory->update($validated);

        return back()->with('status', 'participant-category-updated');
    }

    public function destroy(ParticipantCategory $participantCategory): RedirectResponse
    {
        $inUse = EventRegistration::where('participant_type', $participantCategory->key)->exists()
            || RegistrationFee::where('participant_type', $participantCategory->key)->exists();

        if ($inUse) {
            throw ValidationException::withMessages([
                'key' => 'Kategori ini masih digunakan oleh data pendaftaran atau aturan biaya, dan tidak dapat dihapus. Nonaktifkan saja agar tidak muncul sebagai pilihan baru.',
            ]);
        }

        $participantCategory->delete();

        return back()->with('status', 'participant-category-deleted');
    }
}
