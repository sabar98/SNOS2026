<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\LandingSetting;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function index(): Response
    {
        $setting = LandingSetting::current();

        return Inertia::render('Landing', [
            'seminar' => [
                'name' => $setting->name,
                'theme' => $setting->theme,
                'date_range' => $setting->date_range,
                'location' => $setting->location,
                'scope' => $setting->scope,
                'speakers' => $setting->speakers,
                'timeline' => $setting->timeline,
                'fees' => config('seminar.fees'),
                'organizer' => config('seminar.organizer'),
                'contact' => config('seminar.contact'),
                'leader_message' => $setting->leader_message,
                'partners' => $setting->partners,
            ],
            'journals' => Journal::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
