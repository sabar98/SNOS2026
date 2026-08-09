<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\Payment;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_participants' => EventRegistration::count(),
                'pending_payment_verifications' => Payment::where('status', 'menunggu_verifikasi')->count(),
                'articles_awaiting_administration' => Article::where('status', 'diajukan')->count(),
                'articles_in_review' => Article::whereIn('status', ['proses_review', 'sedang_direview'])->count(),
            ],
            'recentRegistrations' => EventRegistration::with('user')->latest()->limit(10)->get(),
            'participantsByType' => EventRegistration::query()
                ->selectRaw('participant_type, count(*) as total')
                ->groupBy('participant_type')
                ->pluck('total', 'participant_type'),
            'articlesByStatus' => Article::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'articlesByJournal' => Article::query()
                ->whereNotNull('journal_id')
                ->join('journals', 'journals.id', '=', 'articles.journal_id')
                ->selectRaw('journals.name as journal_name, count(*) as total')
                ->groupBy('journals.name')
                ->pluck('total', 'journal_name'),
            'paymentsByStatus' => Payment::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }
}
