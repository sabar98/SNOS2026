<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleDeadlineSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArticleDeadlineSettingController extends Controller
{
    public function edit(): Response
    {
        $setting = ArticleDeadlineSetting::current();

        return Inertia::render('Admin/DeadlineSettings', [
            'setting' => [
                'article_submission_deadline' => $setting->article_submission_deadline->format('Y-m-d\TH:i'),
                'presentation_material_deadline' => $setting->presentation_material_deadline->format('Y-m-d\TH:i'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'article_submission_deadline' => ['required', 'date'],
            'presentation_material_deadline' => ['required', 'date'],
        ]);

        ArticleDeadlineSetting::current()->update($validated);

        return back()->with('status', 'deadline-settings-saved');
    }
}
