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
     * participant_name is computed (not a column) — appended so it's always
     * included wherever a Payment is serialized (admin payment lists, etc.)
     * without every caller having to remember to eager-load and derive it.
     */
    protected $appends = ['participant_name'];

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
     * The name of the participant this payment belongs to, regardless of
     * whether it's a registration fee (payable = EventRegistration, whose
     * owner is directly on it) or a publication fee (payable = Article,
     * whose owner is one hop further via its EventRegistration).
     */
    protected function participantName(): Attribute
    {
        return Attribute::get(function () {
            $payable = $this->payable;

            if ($payable instanceof EventRegistration) {
                return $payable->user?->name;
            }

            if ($payable instanceof Article) {
                return $payable->eventRegistration?->user?->name;
            }

            return null;
        });
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
