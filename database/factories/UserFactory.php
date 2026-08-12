<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::BusinessAdmin,
            'business_id' => Business::factory(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Super-admin: menguasai seluruh anak usaha, jadi tidak terikat ke satu pun.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::SuperAdmin,
            'business_id' => null,
        ]);
    }

    /**
     * Admin yang memegang satu anak usaha tertentu.
     */
    public function forBusiness(Business $business): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::BusinessAdmin,
            'business_id' => $business->id,
        ]);
    }
}
