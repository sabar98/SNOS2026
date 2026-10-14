<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));

        $payments = Payment::with(['payable' => function ($morphTo) {
            $morphTo->morphWith([
                EventRegistration::class => ['user'],
                Article::class => ['eventRegistration.user'],
            ]);
        }])
            ->when($search !== '', function ($query) use ($search) {
                // Referensi can be a registration number or an article title, and the
                // participant can be found via either payable type — registrasi payments
                // reach the user directly, publikasi payments one hop further via the article.
                $query->where(function ($query) use ($search) {
                    $query->where('payment_code', 'like', "%{$search}%")
                        ->orWhereHasMorph('payable', [EventRegistration::class], function ($query) use ($search) {
                            $query->where('registration_number', 'like', "%{$search}%")
                                ->orWhereHas('user', function ($query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                                });
                        })
                        ->orWhereHasMorph('payable', [Article::class], function ($query) use ($search) {
                            $query->where('title', 'like', "%{$search}%")
                                ->orWhereHas('eventRegistration.user', function ($query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Payments', [
            'payments' => $payments,
            'search' => $search,
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
            'decision' => ['required', 'in:terverifikasi,perlu_perbaikan,ditolak,dibatalkan,belum_bayar'],
            'notes' => ['nullable', 'string', 'required_unless:decision,terverifikasi'],
        ]);

        // Captured before the update: whether this payment is the reason the registration
        // is currently marked paid, so an undo (ditolak/dibatalkan/belum_bayar on a payment
        // that was terverifikasi) can safely step the registration back — but only while it
        // hasn't progressed any further (e.g. an article was already submitted), so this
        // never clobbers state that depends on something other than this one payment.
        $canUndoRegistrationPayment = $payment->type === 'registrasi'
            && $payment->status === 'terverifikasi'
            && $payment->payable->status === 'pembayaran_terverifikasi';

        $payment->update([
            'status' => $validated['decision'],
            'notes' => $validated['notes'] ?? null,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        if ($validated['decision'] === 'terverifikasi' && $payment->type === 'registrasi') {
            $payment->payable->update(['status' => 'pembayaran_terverifikasi']);
            $payment->payable->markCompletedIfEligible();
        } elseif ($canUndoRegistrationPayment && in_array($validated['decision'], ['ditolak', 'dibatalkan', 'belum_bayar'], true)) {
            $payment->payable->update(['status' => 'menunggu_pembayaran']);
        }

        return back()->with('status', 'payment-updated');
    }
}
