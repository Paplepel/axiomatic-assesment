<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionNoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id'   => Company::factory(),
            'branch_id'    => Branch::factory(),
            'employee_id'  => Employee::factory(),
            'created_by'   => User::factory(),
            'amount'       => $this->faker->randomFloat(2, 1000, 50000),
            'notes'        => $this->faker->optional()->sentence(),
            'payment_date' => $this->faker->date('Y-m-d', 'now'),
        ];
    }
}
