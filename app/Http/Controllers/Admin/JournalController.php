<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Journal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JournalController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Journals', [
            'journals' => Journal::orderBy('name')->get(),
            'articlesByJournal' => Article::query()
                ->whereNotNull('journal_id')
                ->join('journals', 'journals.id', '=', 'articles.journal_id')
                ->selectRaw('journals.name as journal_name, count(*) as total')
                ->groupBy('journals.name')
                ->pluck('total', 'journal_name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:jurnal,prosiding'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'issn' => ['nullable', 'string', 'max:50'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'publication_fee' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);

        Journal::create($validated + ['is_active' => true]);

        return back()->with('status', 'journal-created');
    }

    public function update(Request $request, Journal $journal): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:jurnal,prosiding'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'issn' => ['nullable', 'string', 'max:50'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'publication_fee' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $journal->update($validated);

        return back()->with('status', 'journal-updated');
    }

    public function destroy(Journal $journal): RedirectResponse
    {
        $journal->delete();

        return back()->with('status', 'journal-deleted');
    }
}
