<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(): Response
    {
        $payments = Payment::with('payable')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Payments', [
            'payments' => $payments,
            'paymentsByStatus' => Payment::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'paymentsByType' => Payment::query()
                ->selectRaw('type, count(*) as total')
                ->groupBy('type')
                ->pluck('total', 'type'),
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:terverifikasi,perlu_perbaikan'],
            'notes' => ['nullable', 'string', 'required_if:decision,perlu_perbaikan'],
        ]);

        $payment->update([
            'status' => $validated['decision'],
            'notes' => $validated['notes'] ?? null,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        if ($validated['decision'] === 'terverifikasi' && $payment->type === 'registrasi') {
            $payment->payable->update(['status' => 'pembayaran_terverifikasi']);
            $payment->payable->markCompletedIfEligible();
        }

        return back()->with('status', 'payment-updated');
    }
}
