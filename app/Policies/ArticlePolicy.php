<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('admin') ? true : null;
    }

    public function view(User $user, Article $article): bool
    {
        return $user->id === $article->eventRegistration->user_id
            || $article->reviewerAssignments()->where('reviewer_id', $user->id)->exists();
    }

    public function update(User $user, Article $article): bool
    {
        return $user->id === $article->eventRegistration->user_id;
    }
}
