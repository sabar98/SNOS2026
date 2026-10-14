<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    /**
     * All computed (not columns) — appended so they're always included wherever
     * a Payment is serialized (admin payment lists, exports, etc.) without every
     * caller having to remember to eager-load and derive them.
     */
    protected $appends = ['participant_name', 'participant_email', 'participant_category', 'reference'];

    /**
     * Keep in sync with resources/js/lib/labels.ts' paymentTypeLabels /
     * paymentStatusLabels — this is the PHP-side copy for exports (Excel/PDF),
     * which can't reach the frontend's TypeScript labels.
     */
    public const TYPE_LABELS = [
        'registrasi' => 'Registrasi',
        'publikasi' => 'Publikasi',
    ];

    public const STATUS_LABELS = [
        'belum_bayar' => 'Belum Bayar',
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'terverifikasi' => 'Terverifikasi',
        'perlu_perbaikan' => 'Perlu Perbaikan',
        'ditolak' => 'Ditolak',
        'dibatalkan' => 'Dibatalkan',
    ];

    /**
     * Cached per-request so listing many payments only loads the category
     * key->label map once instead of once per row.
     */
    protected static ?array $participantCategoryLabels = null;

    protected $fillable = [
        'payable_type',
        'payable_id',
        'type',
        'amount',
        'bank_account',
        'bank_account_id',
        'payment_code',
        'due_at',
        'proof_file_path',
        'status',
        'notes',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The participant this payment belongs to, regardless of whether it's a
     * registration fee (payable = EventRegistration, whose owner is directly
     * on it) or a publication fee (payable = Article, whose owner is one hop
     * further via its EventRegistration).
     */
    public function participant(): ?User
    {
        $payable = $this->payable;

        if ($payable instanceof EventRegistration) {
            return $payable->user;
        }

        if ($payable instanceof Article) {
            return $payable->eventRegistration?->user;
        }

        return null;
    }

    protected function participantName(): Attribute
    {
        return Attribute::get(fn () => $this->participant()?->name);
    }

    protected function participantEmail(): Attribute
    {
        return Attribute::get(fn () => $this->participant()?->email);
    }

    /**
     * The "Kategori" (e.g. Presenter Luring, Peserta Umum) the participant registered
     * under, resolved to its current admin-editable label — falls back to the raw key
     * if the category has since been renamed or removed from Admin > Kategori Peserta.
     */
    protected function participantCategory(): Attribute
    {
        return Attribute::get(function () {
            $type = $this->participantType();

            if ($type === null) {
                return null;
            }

            static::$participantCategoryLabels ??= ParticipantCategory::pluck('label', 'key')->all();

            return static::$participantCategoryLabels[$type] ?? $type;
        });
    }

    /**
     * What identifies this payment to an admin at a glance: the registration number
     * for a registration fee, or the article title for a publication fee.
     */
    protected function reference(): Attribute
    {
        return Attribute::get(fn () => $this->payable?->registration_number ?? $this->payable?->title);
    }

    private function participantType(): ?string
    {
        $payable = $this->payable;

        return match (true) {
            $payable instanceof EventRegistration => $payable->participant_type,
            $payable instanceof Article => $payable->eventRegistration?->participant_type,
            default => null,
        };
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
