<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage employees');
    }

    public function create(User $user): bool
    {
        return $user->can('manage employees');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $employee->created_by === $user->id;
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $employee->created_by === $user->id;
    }
}
