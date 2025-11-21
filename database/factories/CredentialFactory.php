<?php

namespace Database\Factories;

use App\Models\Credential;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Credential>
 */
class CredentialFactory extends Factory
{
    protected $model = Credential::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fscs' => 'FSCS-' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->company(),
            'secrecy' => $this->faker->randomElement(['R', 'S']),
            'credential' => $this->faker->word(),
            'concession' => $this->faker->date(),
            'validity' => $this->faker->dateTimeBetween('+1 month', '+2 years'),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'validity' => now()->subDays(rand(1, 365)),
        ]);
    }

    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'validity' => now()->addDays(rand(1, 30)),
        ]);
    }

    public function reserved(): static
    {
        return $this->state(fn (array $attributes) => [
            'secrecy' => 'R',
        ]);
    }

    public function secret(): static
    {
        return $this->state(fn (array $attributes) => [
            'secrecy' => 'S',
        ]);
    }
}
