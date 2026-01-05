<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_name' => $this->faker->company(),
            'role_title' => $this->faker->jobTitle(),
            'job_url' => $this->faker->optional()->url(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'applied_at' => $this->faker->date(),
            'last_activity_at' => $this->faker->date(),
            'follow_up_at' => $this->faker->optional()->date(),
        ];
    }
}
