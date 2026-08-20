<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
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
