<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * A real TOTP secret shared by factory users, so tests can compute a
     * valid code for it (a made-up string isn't Base32-decodable).
     */
    protected static ?string $twoFactorSecret;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'role' => UserRole::Owner,
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // Most tests exercise the app past login, not the 2FA setup
            // flow itself, so factory users are 2FA-confirmed by default.
            // Use withoutTwoFactor() to test the enforcement/challenge flow.
            'two_factor_secret' => static::$twoFactorSecret ??= (new Google2FA)->generateSecretKey(),
            'two_factor_confirmed_at' => now(),
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

    public function kitchen(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Kitchen,
        ]);
    }

    public function withoutTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }
}
