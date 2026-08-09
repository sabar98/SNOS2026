<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ArticleRevisionController extends Controller
{
    public function store(Request $request, Article $article): RedirectResponse
    {
        $this->authorize('update', $article);

        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:doc,docx,pdf', 'max:10240'],
            'response_to_reviewer' => ['required', 'string'],
        ]);

        $nextVersion = $article->revisions()->max('version_number') + 1;

        $article->revisions()->create([
            'version_number' => $nextVersion,
            'file_path' => $validated['file']->store('article-revisions', 'public'),
            'response_to_reviewer' => $validated['response_to_reviewer'],
            'submitted_at' => now(),
        ]);

        $article->update(['status' => 'sedang_direview']);

        return back()->with('status', 'revision-submitted');
    }
}
