<?php

namespace App\Policies;

use App\Models\ArticleReviewer;
use App\Models\User;

class ArticleReviewerPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function view(User $user, ArticleReviewer $articleReviewer): bool
    {
        return $user->id === $articleReviewer->reviewer_id;
    }

    public function update(User $user, ArticleReviewer $articleReviewer): bool
    {
        return $user->id === $articleReviewer->reviewer_id;
    }
}
