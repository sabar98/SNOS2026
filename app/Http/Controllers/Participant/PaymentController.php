<?php

namespace App\Http\Controllers\Participant;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function uploadProof(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorize('update', $payment);

        $validated = $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        $payment->update([
            'proof_file_path' => $validated['proof']->store('payment-proofs', 'public'),
            'status' => 'menunggu_verifikasi',
            'notes' => null,
        ]);

        if ($payment->type === 'registrasi') {
            $payment->payable->update(['status' => 'menunggu_verifikasi']);
        }

        return back()->with('status', 'payment-proof-uploaded');
    }
}
