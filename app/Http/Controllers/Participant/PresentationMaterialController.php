<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class PresentationMaterialController extends Controller
{
    /**
     * Each field (slide/video/photo/bio) can be submitted independently so the
     * frontend can upload large files one at a time instead of bundling them into
     * a single multi-megabyte request. "Kesediaan" (consent) is only confirmed once
     * every required piece has been saved, across however many requests it took.
     */
    public function store(Request $request, Article $article): RedirectResponse
    {
        $this->authorize('update', $article);

        abort_if(
            now()->greaterThan(Carbon::parse(config('seminar.presentation_material_deadline'))),
            422,
            'Batas waktu unggah materi presentasi telah berakhir.',
        );

        $validated = $request->validate([
            'slide' => ['nullable', 'file', 'mimes:ppt,pptx,pdf', 'max:20480'],
            'video' => ['nullable', 'file', 'mimes:mp4', 'max:51200'],
            'short_bio' => ['nullable', 'string', 'max:1000'],
            'official_photo' => ['nullable', 'image', 'max:2048'],
            'consent' => ['nullable', 'boolean'],
        ]);

        $data = [];

        if ($request->filled('short_bio')) {
            $data['short_bio'] = $validated['short_bio'];
        }

        if ($request->hasFile('slide')) {
            $data['slide_path'] = $request->file('slide')->store('presentation-slides', 'public');
        }

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('presentation-videos', 'public');
        }

        if ($request->hasFile('official_photo')) {
            $data['official_photo_path'] = $request->file('official_photo')->store('presenter-photos', 'public');
        }

        $material = $article->presentationMaterial()->updateOrCreate(['article_id' => $article->id], $data);

        if ($request->boolean('consent')) {
            $missing = [];
            if (! $material->slide_path) {
                $missing['slide'] = 'File PowerPoint wajib diunggah sebelum mengonfirmasi kesediaan.';
            }
            if (! $material->official_photo_path) {
                $missing['official_photo'] = 'Foto resmi wajib diunggah sebelum mengonfirmasi kesediaan.';
            }
            if (! $material->short_bio) {
                $missing['short_bio'] = 'Biodata singkat wajib diisi sebelum mengonfirmasi kesediaan.';
            }

            if ($missing) {
                throw ValidationException::withMessages($missing);
            }

            $material->update(['consent_confirmed_at' => now()]);
        }

        return back()->with('status', 'presentation-material-saved');
    }
}
