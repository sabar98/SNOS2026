<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoaSetting;
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
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'signature' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        $setting = LoaSetting::current();

        if ($setting->signature_path) {
            Storage::disk('public')->delete($setting->signature_path);
        }

        $path = $request->file('signature')->store('loa-signatures', 'public');

        $setting->update(['signature_path' => $path]);

        return back()->with('status', 'loa-signature-saved');
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
