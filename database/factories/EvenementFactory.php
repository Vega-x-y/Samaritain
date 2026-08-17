<?php

namespace Database\Factories;

use App\Enums\EvenementType;
use App\Models\Evenement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evenement>
 */
class EvenementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dateDebut = $this->faker->dateTimeBetween('now', '+1 month');

        return [
            'chantier_id' => null,
            'titre' => $this->faker->sentence(3),
            'date_debut' => $dateDebut,
            'date_fin' => Carbon::parse($dateDebut)->addHour(),
            'type' => $this->faker->randomElement(EvenementType::cases()),
            'description' => $this->faker->optional()->paragraph(),
        ];
    }
}
