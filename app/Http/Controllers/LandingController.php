<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\LandingSetting;
use App\Models\RegistrationFee;
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
                'site_name' => $setting->site_name,
                'site_logo_path' => $setting->site_logo_path,
                'theme' => $setting->theme,
                'date_range' => $setting->date_range,
                'location' => $setting->location,
                'scope' => $setting->scope,
                'speakers' => $setting->speakers,
                'timeline' => $setting->timeline,
                'organizer' => $setting->organizer,
                'contact' => $setting->contact,
                'leader_message' => $setting->leader_message,
                'partners' => $setting->partners,
            ],
            'registrationFees' => RegistrationFee::orderBy('participant_type')->orderBy('attendance_method')->get(['participant_type', 'attendance_method', 'amount']),
            'journals' => Journal::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
