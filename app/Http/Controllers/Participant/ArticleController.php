<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\StoreArticleRequest;
use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\Journal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function create(EventRegistration $registration): Response
    {
        $this->authorize('update', $registration);

        return Inertia::render('Participant/ArticleCreate', [
            'registration' => $registration,
            'journals' => Journal::query()->where('is_active', true)->orderBy('name')->get(),
            'submissionDeadline' => config('seminar.article_submission_deadline'),
            'deadlinePassed' => now()->greaterThan(Carbon::parse(config('seminar.article_submission_deadline'))),
        ]);
    }

    public function store(StoreArticleRequest $request, EventRegistration $registration): RedirectResponse
    {
        abort_if(
            now()->greaterThan(Carbon::parse(config('seminar.article_submission_deadline'))),
            422,
            'Batas waktu pengumpulan artikel telah berakhir.',
        );

        $validated = $request->validated();

        DB::transaction(function () use ($registration, $validated, $request) {
            $article = $registration->articles()->create([
                'journal_id' => $validated['journal_id'] ?? null,
                'title' => $validated['title'],
                'abstract' => $validated['abstract'],
                'keywords' => $validated['keywords'],
                'field' => $validated['field'],
                'file_path' => $request->file('file')->store('articles', 'public'),
                'statement_letter_path' => $request->file('statement_letter')->store('article-statements', 'public'),
                'status' => 'diajukan',
            ]);

            foreach ($validated['authors'] as $index => $author) {
                $article->authors()->create([
                    'name' => $author['name'],
                    'email' => $author['email'],
                    'affiliation' => $author['affiliation'] ?? null,
                    'order' => $index + 1,
                    'is_corresponding' => $author['is_corresponding'] ?? false,
                ]);
            }

            $registration->update(['status' => 'artikel_diajukan']);
        });

        return redirect()->route('participant.registrations.show', $registration)
            ->with('status', 'article-submitted');
    }

    public function show(Article $article): Response
    {
        $this->authorize('view', $article);

        $article->load(['eventRegistration', 'journal', 'authors', 'reviews', 'revisions', 'letterOfAcceptance', 'presentationMaterial', 'publication.journal', 'payments']);

        return Inertia::render('Participant/ArticleShow', [
            'article' => $article,
            'presentationMaterialDeadline' => config('seminar.presentation_material_deadline'),
            'presentationMaterialDeadlinePassed' => now()->greaterThan(Carbon::parse(config('seminar.presentation_material_deadline'))),
        ]);
    }
}
