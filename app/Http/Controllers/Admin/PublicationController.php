<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Journal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Publications', [
            'articles' => Article::query()
                ->where('status', 'diterima')
                ->with(['eventRegistration.user', 'journal', 'publication'])
                ->latest()
                ->get(),
            'journals' => Journal::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Article $article): RedirectResponse
    {
        $validated = $request->validate([
            'journal_id' => ['required', 'exists:journals,id'],
            'status' => ['required', 'in:diproses,terbit'],
            'volume' => ['nullable', 'string', 'max:50'],
            'issue_number' => ['nullable', 'string', 'max:50'],
            'doi' => ['nullable', 'string', 'max:255'],
            'article_url' => ['nullable', 'url', 'max:255'],
        ]);

        $existing = $article->publication;
        $publishedAt = match (true) {
            $validated['status'] !== 'terbit' => null,
            $existing?->published_at !== null => $existing->published_at,
            default => now(),
        };

        $article->publication()->updateOrCreate(
            ['article_id' => $article->id],
            $validated + ['published_at' => $publishedAt],
        );

        return back()->with('status', 'publication-saved');
    }
}
