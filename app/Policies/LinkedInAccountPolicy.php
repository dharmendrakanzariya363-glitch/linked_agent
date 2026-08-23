<?php

namespace App\Policies;

use App\Models\LinkedInAccount;
use App\Models\User;

class LinkedInAccountPolicy
{
    public function view(User $user, LinkedInAccount $account): bool
    {
        return $user->id === $account->user_id;
    }

    public function delete(User $user, LinkedInAccount $account): bool
    {
        return $user->id === $account->user_id;
    }
}
