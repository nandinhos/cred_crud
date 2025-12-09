<?php

namespace Database\Factories;

use App\Enums\CredentialSecrecy;
use App\Enums\CredentialType;
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
        $type = $this->faker->randomElement([CredentialType::CRED, CredentialType::TCMS]);
        $concession = $this->faker->optional(0.7)->dateTimeBetween('-2 years', 'now');

        return [
            'user_id' => User::factory(),
            'fscs' => 'FSCS-'.$this->faker->unique()->numberBetween(1000, 9999),
            'type' => $type,
            'observation' => $this->faker->optional(0.3)->sentence(),
            'secrecy' => $this->faker->randomElement([CredentialSecrecy::RESERVADO, CredentialSecrecy::SECRETO]),
            'credential' => $this->faker->numerify('CRED-####-####'),
            'concession' => $concession,
            // validity será calculado automaticamente pelo Observer se houver concessão
            'validity' => null,
        ];
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
            'secrecy' => CredentialSecrecy::RESERVADO,
        ]);
    }

    public function secret(): static
    {
        return $this->state(fn (array $attributes) => [
            'secrecy' => CredentialSecrecy::SECRETO,
        ]);
    }

    public function cred(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CredentialType::CRED,
        ]);
    }

    public function tcms(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CredentialType::TCMS,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CredentialType::CRED,
            'concession' => null,
            'validity' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CredentialType::CRED,
            'concession' => now()->subMonths(6),
        ]);
    }

    /**
     * Credencial vencida - pode ser substituída por nova
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CredentialType::CRED,
            'concession' => now()->subYears(3),
            'validity' => now()->subDays(30),
        ]);
    }

    /**
     * Credencial negada - pode ter múltiplas por usuário
     */
    public function denied(): static
    {
        return $this->state(function (array $attributes) {
            static $counter = 0;
            $counter++;

            return [
                'fscs' => '00000',
                'type' => CredentialType::CRED,
                'concession' => null,
                'validity' => null,
            ];
        });
    }
}
