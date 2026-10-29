<?php

namespace App\Policies;

use App\Models\ExamResult;
use App\Models\User;

class ExamResultPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->role === User::ROLE_USER;
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, ExamResult $examResult): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, ExamResult $examResult): bool
    {
        return $user->isAdmin();
    }

    public function publish(User $user, ExamResult $examResult): bool
    {
        return $this->viewAny($user);
    }
}
