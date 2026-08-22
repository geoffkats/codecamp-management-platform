<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageUsers($user);
    }

    public function view(User $user, User $model): bool
    {
        return $this->canManageUsers($user);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isSupervisor() || $user->isOperationsManager();
    }

    public function update(User $user, User $model): bool
    {
        return $this->canManageUsers($user);
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        if (! $user->isAdmin() && ! $user->isSupervisor() && ! $user->isOperationsManager()) {
            return false;
        }

        if ($model->isAdmin() && ! $user->isAdmin()) {
            return false;
        }

        return true;
    }

    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->isSupervisor();
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    private function canManageUsers(User $user): bool
    {
        return $user->hasPermission('manage_users')
            || $user->isAdmin()
            || $user->isOperationsManager()
            || $user->isTeacher();
    }
}
