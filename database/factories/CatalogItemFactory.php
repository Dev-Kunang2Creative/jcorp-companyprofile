<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\CatalogItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CatalogItem>
 */
class CatalogItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(10, 500) * 1000,
            'price_note' => null,
            'is_available' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * Disembunyikan sementara dari halaman publik — datanya tetap utuh.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }

    public function withoutPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => null,
            'price_note' => null,
        ]);
    }
}
