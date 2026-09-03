<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class WelcomeAccountMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $plainPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akun '.config('seminar.name').' Berhasil Dibuat',
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
        return new Content(
            view: 'emails.welcome-account',
            with: [
                'seminarName' => config('seminar.name'),
                'loginUrl' => route('login'),
            ],
        );
    }
}
