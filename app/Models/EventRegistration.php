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

    // Keep in sync with resources/js/lib/labels.ts (participantTypeLabels).
    public const PARTICIPANT_TYPE_LABELS = [
        'presenter_luring' => 'Presenter Luring',
        'presenter_daring' => 'Presenter Daring',
        'peserta_umum' => 'Peserta Umum',
        'peserta_mahasiswa' => 'Peserta Mahasiswa',
    ];

    // Keep in sync with resources/js/lib/labels.ts (attendanceMethodLabels).
    public const ATTENDANCE_METHOD_LABELS = [
        'luring' => 'Luring',
        'daring' => 'Daring',
    ];

    // Keep in sync with resources/js/lib/labels.ts (registrationStatusLabels).
    public const STATUS_LABELS = [
        'draft' => 'Draft',
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'pembayaran_terverifikasi' => 'Pembayaran Terverifikasi',
        'artikel_diajukan' => 'Artikel Diajukan',
        'sedang_direview' => 'Sedang Direview',
        'perlu_revisi' => 'Perlu Revisi',
        'artikel_diterima' => 'Artikel Diterima',
        'jadwal_ditetapkan' => 'Jadwal Ditetapkan',
        'siap_mengikuti_seminar' => 'Siap Mengikuti Seminar',
        'hadir' => 'Hadir',
        'selesai' => 'Selesai',
        'sertifikat_terbit' => 'Sertifikat Terbit',
        'ditolak' => 'Ditolak',
    ];

    protected $fillable = [
        'registration_number',
        'user_id',
        'participant_type',
        'attendance_method',
        'article_scope',
        'institution',
        'special_needs',
        'join_gala_dinner',
        'join_wisata_sabang',
        'join_wisata_lokal',
        'terms_accepted_at',
        'status',
        'payment_due_at',
    ];

    protected function casts(): array
    {
        return [
            'join_gala_dinner' => 'boolean',
            'join_wisata_sabang' => 'boolean',
            'join_wisata_lokal' => 'boolean',
            'terms_accepted_at' => 'datetime',
            'payment_due_at' => 'datetime',
        ];
    }

    public function isPresenter(): bool
    {
        return (bool) ParticipantCategory::where('key', $this->participant_type)->value('is_presenter');
    }

    /**
     * Once the registration fee payment is verified, participant_type can no longer
     * change (it would invalidate the amount already paid and verified).
     */
    public function isFeeLocked(): bool
    {
        return $this->hasVerifiedRegistrationPayment();
    }

    /**
     * Whether the registration fee has actually been paid and verified by the
     * panitia. Article submission (and anything else gated on "has paid") should
     * check this — not the loosely-related `status` column, which can drift.
     */
    public function hasVerifiedRegistrationPayment(): bool
    {
        return $this->payments()->where('type', 'registrasi')->where('status', 'terverifikasi')->exists();
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
