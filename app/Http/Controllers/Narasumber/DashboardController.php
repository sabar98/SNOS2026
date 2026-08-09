<?php

namespace App\Http\Controllers\Narasumber;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();

        return Inertia::render('Narasumber/Dashboard', [
            'certificates' => $user->certificates()->where('role', 'narasumber')->latest()->get(),
        ]);
    }
}
