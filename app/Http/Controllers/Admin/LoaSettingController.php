<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterOfAcceptance;
use App\Models\LoaSetting;
use App\Services\LoaPdfGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LoaSettingController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/LoaSettings', [
            'setting' => LoaSetting::current(),
            'issuedCount' => LetterOfAcceptance::query()->whereNotNull('file_path')->count(),
        ]);
    }

    /**
     * Re-renders the stored PDF of every LoA that was already issued, so kop surat,
     * signer name and signature changes reach documents that were issued earlier.
     * The loa_number and issued_at are left untouched.
     */
    public function regenerate(LoaPdfGenerator $loaPdfGenerator): RedirectResponse
    {
        $count = 0;

        LetterOfAcceptance::query()
            ->whereNotNull('file_path')
            ->chunkById(50, function ($letters) use ($loaPdfGenerator, &$count) {
                foreach ($letters as $loa) {
                    $loaPdfGenerator->generate($loa);
                    $count++;
                }
            });

        return back()->with('status', 'loa-regenerated:'.$count);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'signer_name' => ['required', 'string', 'max:255'],
            'signer_title' => ['nullable', 'string', 'max:255'],
            'signature' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        $setting = LoaSetting::current();

        $setting->fill([
            'signer_name' => $validated['signer_name'],
            'signer_title' => $validated['signer_title'] ?? null,
        ]);

        if ($request->hasFile('signature')) {
            if ($setting->signature_path) {
                Storage::disk('public')->delete($setting->signature_path);
            }

            $setting->signature_path = $request->file('signature')->store('loa-signatures', 'public');
        }

        $setting->save();

        return back()->with('status', 'loa-settings-saved');
    }

    public function destroy(): RedirectResponse
    {
        $setting = LoaSetting::current();

        if ($setting->signature_path) {
            Storage::disk('public')->delete($setting->signature_path);
            $setting->update(['signature_path' => null]);
        }

        return back()->with('status', 'loa-signature-removed');
    }
}
