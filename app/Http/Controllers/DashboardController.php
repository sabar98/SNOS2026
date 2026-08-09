<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Redirect the authenticated user to the dashboard for their primary role.
     */
    public function index(): RedirectResponse
    {
        $user = Auth::user();

        return match (true) {
            $user->hasRole('admin') => redirect()->route('admin.dashboard'),
            $user->hasRole('reviewer') => redirect()->route('reviewer.articles.index'),
            $user->hasRole('moderator') => redirect()->route('moderator.sessions.index'),
            $user->hasRole('pimpinan') => redirect()->route('pimpinan.dashboard'),
            $user->hasRole('narasumber') => redirect()->route('narasumber.dashboard'),
            default => redirect()->route('participant.dashboard'),
        };
    }
}
