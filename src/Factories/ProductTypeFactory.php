<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\ProductType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductTypeFactory extends Factory
{
    protected $model = ProductType::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => $this->faker->words(2, true),
                'hr' => $this->faker->words(2, true),
                'sl' => $this->faker->words(2, true),
                'sr' => $this->faker->words(2, true),
            ],
            'code' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{3}'),
        ];
    }

    public function withName(string|array $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => is_string($name) ? ['en' => $name] : $name,
        ]);
    }

    public function withCode(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => $code,
        ]);
    }
}
