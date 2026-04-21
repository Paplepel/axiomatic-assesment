<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'branch_id'  => Branch::factory(),
            'name'       => $this->faker->name(),
            'email'      => $this->faker->unique()->safeEmail(),
            'created_by' => User::factory(),
        ];
    }
}
