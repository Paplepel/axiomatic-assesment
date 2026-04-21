<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name'       => $this->faker->city() . ' Branch',
            'address'    => $this->faker->address(),
        ];
    }
}
