<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Landing', [
            'seminar' => config('seminar'),
            'journals' => Journal::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
