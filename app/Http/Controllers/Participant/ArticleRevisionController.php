<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleRevisionController extends Controller
{
    public function store(Request $request, Article $article): RedirectResponse
    {
        $this->authorize('update', $article);

        abort_unless(in_array($article->status, ['revisi_minor', 'revisi_mayor'], true), 422, 'Artikel ini tidak sedang menunggu revisi.');

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:doc,docx,pdf', 'max:10240'],
            'response_to_reviewer' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($article, $validated) {
            $filePath = $validated['file']->store('article-revisions', 'public');
            $nextVersion = $article->revisions()->max('version_number') + 1;

            $article->revisions()->create([
                'version_number' => $nextVersion,
                'file_path' => $filePath,
                'response_to_reviewer' => $validated['response_to_reviewer'],
                'submitted_at' => now(),
            ]);

            // Without this, admins and reviewers keep seeing the original file forever —
            // the revision was only ever saved to article_revisions, never surfaced back
            // to the article record that their screens actually render.
            $article->update(['file_path' => $filePath, 'status' => 'sedang_direview']);

            // The reviewer already marked their assignment "selesai" for the old file;
            // send it back to their queue so the revised file gets a fresh look.
            $article->reviewerAssignments()->where('status', 'selesai')->update(['status' => 'ditugaskan']);
        });

        return back()->with('status', 'revision-submitted');
    }
}
