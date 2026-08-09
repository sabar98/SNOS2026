<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateTemplateController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:peserta,presenter,moderator,reviewer,narasumber,panitia'],
            'template_file' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $existing = CertificateTemplate::where('role', $validated['role'])->first();

        $path = $request->file('template_file')->store('certificate-templates', 'public');

        if ($existing) {
            Storage::disk('public')->delete($existing->file_path);
            $existing->update(['file_path' => $path]);
        } else {
            CertificateTemplate::create([
                'role' => $validated['role'],
                'file_path' => $path,
            ]);
        }

        return back()->with('status', 'certificate-template-saved');
    }

    public function destroy(CertificateTemplate $certificateTemplate): RedirectResponse
    {
        Storage::disk('public')->delete($certificateTemplate->file_path);
        $certificateTemplate->delete();

        return back()->with('status', 'certificate-template-removed');
    }
}
