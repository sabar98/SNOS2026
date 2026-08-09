<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\EventRegistration;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function update(User $user, Payment $payment): bool
    {
        $ownerId = match (true) {
            $payment->payable instanceof EventRegistration => $payment->payable->user_id,
            $payment->payable instanceof Article => $payment->payable->eventRegistration->user_id,
            default => null,
        };

        return $user->id === $ownerId;
    }
}
