<?php

namespace Database\Factories;

use App\Models\OwnerDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OwnerDocument>
 */
class OwnerDocumentFactory extends Factory
{
    protected $model = OwnerDocument::class;

    public function definition(): array
    {
        return [
            'property_id' => null,
            'name' => $this->faker->words(3, true),
            'category' => $this->faker->randomElement(['invoice', 'receipt', 'quote', 'inspection', 'other']),
            'file_path' => 'documents/owner/'.$this->faker->uuid().'.pdf',
            'file_size' => $this->faker->numberBetween(10000, 10000000),
            'documentable_id' => null,
            'documentable_type' => null,
            'created_by' => User::factory(),
        ];
    }
}