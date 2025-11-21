<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'name' => $firstName,
            'full_name' => $firstName.' '.$lastName,
            'rank_id' => \App\Models\Rank::inRandomOrder()->first()?->id,
            'office_id' => \App\Models\Office::inRandomOrder()->first()?->id,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withRole(string $role): static
    {
        return $this->afterCreating(function (\App\Models\User $user) use ($role) {
            $user->assignRole($role);
        });
    }

    public function superAdmin(): static
    {
        return $this->withRole('super_admin');
    }

    public function admin(): static
    {
        return $this->withRole('admin');
    }

    public function consulta(): static
    {
        return $this->withRole('consulta');
    }
}
