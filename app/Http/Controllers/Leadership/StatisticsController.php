<?php

namespace App\Http\Controllers\Leadership;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\Payment;
use App\Models\Publication;
use Inertia\Inertia;
use Inertia\Response;

class StatisticsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Leadership/Statistics', [
            'stats' => [
                'total_participants' => EventRegistration::count(),
                'total_revenue' => Payment::where('status', 'terverifikasi')->sum('amount'),
                'total_articles' => Article::count(),
                'articles_accepted' => Article::where('status', 'diterima')->count(),
                'total_publications' => Publication::count(),
                'published_publications' => Publication::where('status', 'terbit')->count(),
            ],
            'participantsByType' => EventRegistration::query()
                ->selectRaw('participant_type, count(*) as total')
                ->groupBy('participant_type')
                ->pluck('total', 'participant_type'),
        ]);
    }
}
