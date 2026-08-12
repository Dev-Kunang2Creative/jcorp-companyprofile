<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'name' => $name,
            // Bukan catchPhrase() — formatter itu hanya ada di locale en_US,
            // sedangkan APP_FAKER_LOCALE project ini id_ID.
            'tagline' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'catalog_label' => 'Katalog',
            'portfolio_label' => 'Portfolio',
            'whatsapp' => '628'.fake()->numerify('##########'),
            'is_parent' => false,
            'is_published' => true,
            'sort_order' => 0,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    /**
     * Anak usaha yang memamerkan hasil kerja (nail art, eyelash).
     *
     * Bawaannya mati — hanya sebagian anak usaha yang memakai portfolio,
     * jadi test yang memerlukannya harus menyebutnya secara eksplisit.
     */
    public function withPortfolio(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_portfolio' => true,
        ]);
    }

    public function parent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_parent' => true,
        ]);
    }
}
