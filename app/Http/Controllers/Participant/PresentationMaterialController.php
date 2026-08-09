<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PresentationMaterialController extends Controller
{
    public function store(Request $request, Article $article): RedirectResponse
    {
        $this->authorize('update', $article);

        abort_if(
            now()->greaterThan(Carbon::parse(config('seminar.presentation_material_deadline'))),
            422,
            'Batas waktu unggah materi presentasi telah berakhir.',
        );

        $existing = $article->presentationMaterial;

        $validated = $request->validate([
            'slide' => [$existing?->slide_path ? 'nullable' : 'required', 'file', 'mimes:ppt,pptx,pdf', 'max:20480'],
            'video' => ['nullable', 'file', 'mimes:mp4', 'max:51200'],
            'short_bio' => ['required', 'string', 'max:1000'],
            'official_photo' => [$existing?->official_photo_path ? 'nullable' : 'required', 'image', 'max:2048'],
            'consent' => ['required', 'accepted'],
        ]);

        $data = [
            'short_bio' => $validated['short_bio'],
            'consent_confirmed_at' => now(),
        ];

        if ($request->hasFile('slide')) {
            $data['slide_path'] = $request->file('slide')->store('presentation-slides', 'public');
        }

        if ($request->hasFile('video')) {
            $data['video_path'] = $request->file('video')->store('presentation-videos', 'public');
        }

        if ($request->hasFile('official_photo')) {
            $data['official_photo_path'] = $request->file('official_photo')->store('presenter-photos', 'public');
        }

        $article->presentationMaterial()->updateOrCreate(['article_id' => $article->id], $data);

        return back()->with('status', 'presentation-material-saved');
    }
}
