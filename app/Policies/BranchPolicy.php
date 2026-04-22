<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    public function create(User $user): bool
    {
        return $user->can('manage branches');
    }

    public function update(User $user, Branch $branch): bool
    {
        return $branch->created_by === $user->id;
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $branch->created_by === $user->id;
    }
}
