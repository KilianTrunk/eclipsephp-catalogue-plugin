<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\Category;
use Eclipse\Core\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(),
            'sort' => $this->faker->randomNumber(),
            'is_active' => $this->faker->boolean(),
            'recursive_browsing' => $this->faker->boolean(),
            'sef_key' => $this->faker->word(),
            'short_desc' => $this->faker->words(10),
            'description' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'site_id' => Site::inRandomOrder()->first()?->id ?? Site::factory()->create()->id,
        ];
    }
}
