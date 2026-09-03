<?php

namespace App\Mail;

use App\Models\EventRegistration;
use App\Models\LandingSetting;
use App\Models\ParticipantCategory;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class RegistrationPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EventRegistration $registration,
        public Payment $payment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Info Pembayaran Pendaftaran '.$this->registration->registration_number,
        );
    }

    /**
     * Hints to mail clients (Gmail in particular silently auto-translates a message
     * once a viewer has ever accepted "always translate" for a language) that this
     * message is written in Indonesian, not English.
     */
    public function headers(): Headers
    {
        return new Headers(
            text: ['Content-Language' => 'id'],
        );
    }

    public function content(): Content
    {
        $categoryLabel = ParticipantCategory::where('key', $this->registration->participant_type)->value('label')
            ?? $this->registration->participant_type;

        return new Content(
            view: 'emails.registration-payment',
            with: [
                'seminarName' => LandingSetting::current()->name,
                'userName' => $this->registration->user->name,
                'registrationNumber' => $this->registration->registration_number,
                'categoryLabel' => $categoryLabel,
                'amount' => (float) $this->payment->amount,
                'bankAccount' => $this->payment->bank_account,
                'paymentCode' => $this->payment->payment_code,
                // Formatted explicitly in Indonesian regardless of APP_LOCALE, since the
                // rest of this app's UI and copy is Indonesian.
                'dueAt' => $this->payment->due_at?->locale('id')->translatedFormat('d F Y, H:i'),
                'detailUrl' => route('participant.registrations.show', $this->registration),
            ],
        );
    }
}
