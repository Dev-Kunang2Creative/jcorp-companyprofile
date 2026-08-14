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
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Akses dimatikan super-admin. Datanya utuh, hanya tidak bisa masuk.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Akun yang sudah diundang tapi undangannya belum dibuka.
     *
     * Ditandai `password` null — itu yang membedakannya dari akun aktif.
     * Tokennya disimpan sebagai hash, sama seperti di InvitationService.
     */
    public function invited(string $token = 'token-undangan-uji'): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => null,
            'invitation_token' => hash('sha256', $token),
            'invitation_expires_at' => now()->addDays(User::INVITATION_VALID_DAYS),
        ]);
    }

    /**
     * Undangan yang sudah lewat masa berlakunya.
     */
    public function invitationExpired(string $token = 'token-kedaluwarsa'): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => null,
            'invitation_token' => hash('sha256', $token),
            'invitation_expires_at' => now()->subDay(),
        ]);
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
