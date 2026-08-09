<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\Payment;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Reports', [
            'participantsByType' => EventRegistration::query()
                ->selectRaw('participant_type, count(*) as total')
                ->groupBy('participant_type')
                ->pluck('total', 'participant_type'),
            'revenue' => Payment::where('status', 'terverifikasi')->sum('amount'),
            'articlesByStatus' => Article::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'attendanceCount' => EventRegistration::where('status', 'hadir')->orWhere('status', 'selesai')->count(),
        ]);
    }
}
