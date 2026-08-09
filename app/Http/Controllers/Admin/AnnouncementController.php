<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Announcements', [
            'announcements' => Announcement::with('creator')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:pengumuman,dokumentasi,best_paper,best_presenter,best_poster'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'link_url' => ['nullable', 'url', 'max:255'],
            'published_at' => ['required', 'date'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
        ]);

        $filePath = null;

        if ($request->hasFile('thumbnail')) {
            $filePath = $request->file('thumbnail')->store('announcements', 'public');
        }

        unset($validated['thumbnail']);

        Announcement::create($validated + [
            'created_by' => Auth::id(),
            'file_path' => $filePath,
        ]);

        return back()->with('status', 'announcement-created');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return back()->with('status', 'announcement-deleted');
    }
}
