<?php

namespace App\Models;

use Database\Factories\EventRegistrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class EventRegistration extends Model
{
    /** @use HasFactory<EventRegistrationFactory> */
    use HasFactory;

    protected $fillable = [
        'registration_number',
        'user_id',
        'participant_type',
        'attendance_method',
        'article_scope',
        'institution',
        'special_needs',
        'join_gala_dinner',
        'terms_accepted_at',
        'status',
        'payment_due_at',
    ];

    protected function casts(): array
    {
        return [
            'join_gala_dinner' => 'boolean',
            'terms_accepted_at' => 'datetime',
            'payment_due_at' => 'datetime',
        ];
    }

    public function isPresenter(): bool
    {
        return in_array($this->participant_type, ['presenter_luring', 'presenter_daring'], true);
    }

    /**
     * Mirrors the checklist in snos.md §19: paid, present, evaluated, and
     * (for presenters) an accepted article — all four unlock the certificate.
     */
    public function isCertificateEligible(): bool
    {
        $this->load(['payments', 'attendances', 'evaluation', 'articles']);

        $isPaid = $this->payments->where('type', 'registrasi')->contains(fn ($payment) => $payment->status === 'terverifikasi');
        $isPresent = $this->attendances->contains(fn ($attendance) => $attendance->status === 'hadir');
        $hasEvaluated = (bool) $this->evaluation;
        $presentationDone = ! $this->isPresenter() || $this->articles->contains(fn ($article) => $article->status === 'diterima');

        return $isPaid && $isPresent && $hasEvaluated && $presentationDone;
    }

    /**
     * Advances status to "selesai" once every certificate prerequisite is met.
     * Safe to call after any of the three qualifying actions (payment
     * verification, check-in, evaluation) since order between them varies.
     */
    public function markCompletedIfEligible(): void
    {
        if ($this->status !== 'sertifikat_terbit' && $this->isCertificateEligible()) {
            $this->update(['status' => 'selesai']);
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function evaluation(): HasOne
    {
        return $this->hasOne(Evaluation::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
