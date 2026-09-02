<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LandingSettingController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/LandingSettings', [
            'setting' => LandingSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'site_name' => ['required', 'string', 'max:100'],
            'site_logo_path' => ['nullable', 'string'],
            'site_logo' => ['nullable', 'image', 'max:2048'],
            'organizer' => ['required', 'string', 'max:255'],
            'contact.email' => ['required', 'email', 'max:255'],
            'contact.phone' => ['required', 'string', 'max:50'],
            'contact.facebook' => ['nullable', 'string', 'max:255'],
            'contact.instagram' => ['nullable', 'string', 'max:255'],
            'contact.address' => ['required', 'string', 'max:500'],
            'theme' => ['required', 'string', 'max:255'],
            'date_range' => ['required', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'scope' => ['required', 'array', 'min:1'],
            'scope.*' => ['required', 'string', 'max:255'],
            'speakers' => ['present', 'array'],
            'speakers.*.name' => ['required', 'string', 'max:255'],
            'speakers.*.title' => ['required', 'string', 'max:255'],
            'speakers.*.topic' => ['nullable', 'string', 'max:255'],
            'speakers.*.photo_path' => ['nullable', 'string'],
            'speakers.*.photo' => ['nullable', 'image', 'max:2048'],
            'timeline' => ['present', 'array'],
            'timeline.*.label' => ['required', 'string', 'max:255'],
            'timeline.*.date' => ['required', 'string', 'max:255'],
            'leader_message.name' => ['required', 'string', 'max:255'],
            'leader_message.title' => ['required', 'string', 'max:255'],
            'leader_message.message' => ['required', 'string', 'max:2000'],
            'leader_message.photo_path' => ['nullable', 'string'],
            'leader_message.photo' => ['nullable', 'image', 'max:2048'],
            'partners' => ['present', 'array'],
            'partners.*.name' => ['required', 'string', 'max:255'],
            'partners.*.logo_path' => ['nullable', 'string'],
            'partners.*.logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $setting = LandingSetting::current();

        $siteLogoPath = $validated['site_logo_path'] ?? null;

        if ($request->hasFile('site_logo')) {
            if ($siteLogoPath) {
                Storage::disk('public')->delete($siteLogoPath);
            }
            $siteLogoPath = $request->file('site_logo')->store('branding', 'public');
        }

        $speakers = collect($validated['speakers'])->map(function (array $speaker, int $index) use ($request) {
            $photoPath = $speaker['photo_path'] ?? null;

            if ($request->hasFile("speakers.{$index}.photo")) {
                if ($photoPath) {
                    Storage::disk('public')->delete($photoPath);
                }
                $photoPath = $request->file("speakers.{$index}.photo")->store('speakers', 'public');
            }

            return [
                'name' => $speaker['name'],
                'title' => $speaker['title'],
                'topic' => $speaker['topic'] ?? null,
                'photo_path' => $photoPath,
            ];
        })->all();

        $leaderPhotoPath = $validated['leader_message']['photo_path'] ?? null;

        if ($request->hasFile('leader_message.photo')) {
            if ($leaderPhotoPath) {
                Storage::disk('public')->delete($leaderPhotoPath);
            }
            $leaderPhotoPath = $request->file('leader_message.photo')->store('leaders', 'public');
        }

        $partners = collect($validated['partners'])->map(function (array $partner, int $index) use ($request) {
            $logoPath = $partner['logo_path'] ?? null;

            if ($request->hasFile("partners.{$index}.logo")) {
                if ($logoPath) {
                    Storage::disk('public')->delete($logoPath);
                }
                $logoPath = $request->file("partners.{$index}.logo")->store('partners', 'public');
            }

            return [
                'name' => $partner['name'],
                'logo_path' => $logoPath,
            ];
        })->all();

        $setting->update([
            'name' => $validated['name'],
            'site_name' => $validated['site_name'],
            'site_logo_path' => $siteLogoPath,
            'organizer' => $validated['organizer'],
            'contact' => $validated['contact'],
            'theme' => $validated['theme'],
            'date_range' => $validated['date_range'],
            'location' => $validated['location'],
            'scope' => $validated['scope'],
            'speakers' => $speakers,
            'timeline' => $validated['timeline'],
            'leader_message' => [
                'name' => $validated['leader_message']['name'],
                'title' => $validated['leader_message']['title'],
                'message' => $validated['leader_message']['message'],
                'photo_path' => $leaderPhotoPath,
            ],
            'partners' => $partners,
        ]);

        return back()->with('status', 'landing-settings-updated');
    }
}
