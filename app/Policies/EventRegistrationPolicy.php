<?php

namespace App\Policies;

use App\Models\EventRegistration;
use App\Models\User;

class EventRegistrationPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function view(User $user, EventRegistration $eventRegistration): bool
    {
        return $user->id === $eventRegistration->user_id;
    }

    public function update(User $user, EventRegistration $eventRegistration): bool
    {
        return $user->id === $eventRegistration->user_id;
    }
}
