<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        $registrations = $user->eventRegistrations()
            ->with(['payments', 'articles.journal', 'evaluation', 'certificates'])
            ->latest()
            ->get();

        return Inertia::render('Participant/Dashboard', [
            'profileComplete' => (bool) $user->profile?->is_complete,
            'registrations' => $registrations,
        ]);
    }
}
