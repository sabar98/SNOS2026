<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\ArticleReviewer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function index(): Response
    {
        $assignments = ArticleReviewer::where('reviewer_id', Auth::id())
            ->with(['article', 'review'])
            ->latest()
            ->get();

        return Inertia::render('Reviewer/ArticleList', [
            'assignments' => $assignments,
        ]);
    }

    public function show(ArticleReviewer $assignment): Response
    {
        $this->authorize('view', $assignment);

        $assignment->load(['article.authors', 'review']);

        return Inertia::render('Reviewer/ArticleShow', [
            'assignment' => $assignment,
        ]);
    }

    public function store(Request $request, ArticleReviewer $assignment): RedirectResponse
    {
        $this->authorize('update', $assignment);

        $validated = $request->validate([
            'theme_suitability_score' => ['required', 'integer', 'min:1', 'max:5'],
            'novelty_score' => ['required', 'integer', 'min:1', 'max:5'],
            'methodology_score' => ['required', 'integer', 'min:1', 'max:5'],
            'results_discussion_score' => ['required', 'integer', 'min:1', 'max:5'],
            'reference_quality_score' => ['required', 'integer', 'min:1', 'max:5'],
            'language_grammar_score' => ['required', 'integer', 'min:1', 'max:5'],
            'comments' => ['nullable', 'string'],
            'recommendation' => ['required', 'in:diterima_tanpa_revisi,diterima_revisi_minor,revisi_mayor,ditolak'],
        ]);

        $assignment->review()->updateOrCreate([], $validated + ['reviewed_at' => now()]);
        $assignment->update(['status' => 'selesai']);

        $articleStatus = match ($validated['recommendation']) {
            'diterima_tanpa_revisi' => 'diterima',
            'diterima_revisi_minor' => 'revisi_minor',
            'revisi_mayor' => 'revisi_mayor',
            'ditolak' => 'ditolak',
        };

        $assignment->article->update(['status' => $articleStatus]);

        if (in_array($articleStatus, ['revisi_minor', 'revisi_mayor', 'ditolak'], true)) {
            $assignment->article->eventRegistration->update(['status' => 'perlu_revisi']);
        }

        return redirect()->route('reviewer.articles.index')->with('status', 'review-submitted');
    }
}
