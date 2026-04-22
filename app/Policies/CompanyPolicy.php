<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage companies');
    }

    public function create(User $user): bool
    {
        return $user->can('manage companies');
    }

    public function update(User $user, Company $company): bool
    {
        return $company->created_by === $user->id;
    }

    public function delete(User $user, Company $company): bool
    {
        return $company->created_by === $user->id;
    }
}
