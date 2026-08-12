<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\PortfolioItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortfolioItem>
 */
class PortfolioItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'image_path' => 'portfolio/'.fake()->uuid().'.webp',
            'caption' => fake()->sentence(4),
            'sort_order' => 0,
        ];
    }
}
