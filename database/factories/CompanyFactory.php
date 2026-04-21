<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'                => $this->faker->company(),
            'registration_number' => strtoupper($this->faker->bothify('??######')),
            'created_by'          => User::factory(),
        ];
    }
}
