<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Brazzaville'],
            ['name' => 'Pointe-Noire'],
            ['name' => 'Dolisie'],
            ['name' => 'Nkayi'],
            ['name' => 'Owando'],
            ['name' => 'Ouesso'],
            ['name' => 'Madingou'],
            ['name' => 'Gamboma'],
            ['name' => 'Impfondo'],
            ['name' => 'Sibiti'],
        ];

        foreach ($cities as $city) {
            City::create($city);
        }
    }
}