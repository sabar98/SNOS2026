<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleReviewer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ReviewerAssignmentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/ReviewerAssignments', [
            'articles' => Article::whereIn('status', ['proses_review', 'sedang_direview'])
                ->with(['eventRegistration.user', 'reviewerAssignments.reviewer'])
                ->get(),
            'reviewers' => User::role('reviewer')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'article_id' => ['required', 'exists:articles,id'],
            'reviewer_id' => ['required', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'is_anonymous' => ['boolean'],
        ]);

        ArticleReviewer::create([
            'article_id' => $validated['article_id'],
            'reviewer_id' => $validated['reviewer_id'],
            'assigned_by' => Auth::id(),
            'due_date' => $validated['due_date'] ?? null,
            'is_anonymous' => $validated['is_anonymous'] ?? true,
            'status' => 'ditugaskan',
        ]);

        Article::whereKey($validated['article_id'])->update(['status' => 'sedang_direview']);

        return back()->with('status', 'reviewer-assigned');
    }

    public function destroy(ArticleReviewer $reviewerAssignment): RedirectResponse
    {
        $reviewerAssignment->delete();

        return back()->with('status', 'reviewer-unassigned');
    }
}
