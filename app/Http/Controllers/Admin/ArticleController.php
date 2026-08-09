<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\LoaPdfGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(): Response
    {
        $articles = Article::with(['eventRegistration.user', 'journal'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Articles', [
            'articles' => $articles,
            'articlesByStatus' => Article::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function show(Article $article): Response
    {
        $article->load(['eventRegistration.user', 'journal', 'authors', 'reviewerAssignments.reviewer', 'reviews', 'revisions', 'letterOfAcceptance', 'presentationMaterial']);

        return Inertia::render('Admin/ArticleShow', [
            'article' => $article,
        ]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:proses_review,perlu_perbaikan_administrasi,ditolak_administrasi'],
            'similarity_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $article->update([
            'status' => $validated['decision'] === 'proses_review' ? 'proses_review' : $validated['decision'],
            'similarity_score' => $validated['similarity_score'] ?? $article->similarity_score,
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        if ($validated['decision'] === 'proses_review') {
            $article->eventRegistration->update(['status' => 'sedang_direview']);
        }

        return back()->with('status', 'article-updated');
    }

    public function issueLoa(Article $article, LoaPdfGenerator $loaPdfGenerator): RedirectResponse
    {
        abort_if($article->letterOfAcceptance, 422, 'LoA sudah diterbitkan.');

        $article->load('journal', 'eventRegistration.user');

        DB::transaction(function () use ($article, $loaPdfGenerator) {
            $article->update(['status' => 'diterima', 'article_number' => $article->article_number ?? 'ART-'.Str::upper(Str::random(6))]);

            $loa = $article->letterOfAcceptance()->create([
                'loa_number' => 'LOA-SNOS2026-'.Str::upper(Str::random(8)),
                'issued_at' => now(),
            ]);

            $loa->update(['file_path' => $loaPdfGenerator->generate($loa)]);

            if ($article->journal && $article->journal->publication_fee > 0) {
                $article->payments()->create([
                    'type' => 'publikasi',
                    'amount' => $article->journal->publication_fee,
                    'bank_account' => '1234567890 a.n. Panitia SNOS 2026 (Bank Contoh)',
                    'payment_code' => 'PAY-'.Str::upper(Str::random(10)),
                    'due_at' => now()->addDays(14),
                    'status' => 'belum_bayar',
                ]);
            }

            $article->eventRegistration->update(['status' => 'artikel_diterima']);
            $article->eventRegistration->markCompletedIfEligible();
        });

        return back()->with('status', 'loa-issued');
    }
}
