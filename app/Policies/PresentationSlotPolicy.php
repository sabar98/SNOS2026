<?php

namespace App\Policies;

use App\Models\PresentationSlot;
use App\Models\User;

class PresentationSlotPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function update(User $user, PresentationSlot $presentationSlot): bool
    {
        return $user->id === $presentationSlot->scheduleSession->moderator_id;
    }
}
