<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price_per_night' => $this->faker->numberBetween(5000, 200000),
            'price_per_hour' => $this->faker->numberBetween(500, 5000),
            'star_rating' => $this->faker->numberBetween(1, 5),
            'rooms' => $this->faker->numberBetween(1, 20),
            'bathrooms' => $this->faker->numberBetween(1, 10),
            'furnished' => $this->faker->boolean(),
            'address' => $this->faker->address(),
            'status' => 'available',
            'is_verify' => $this->faker->boolean(),
            'is_active' => true,
        ];
    }
}
