<?php

namespace Database\Seeders;

use App\Models\Bureau;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;

class BureauSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        $city = City::first();

        $bureaux = [
            [
                'name' => 'Siège Social Corporate',
                'description' => 'Espace de bureau haut de gamme entièrement aménagé avec salle de réunion et accueil.',
                'price' => 1500000,
                'price_type' => 'monthly',
                'surface' => 200,
                'rooms' => 5,
                'address' => '100 Boulevard du 30 Juin',
                'location' => 'Gombe, Kinshasa',
                'phone' => '+243 821 000 111',
                'email' => 'contact@corporate-bureau.cd',
                'active' => true,
                'city_id' => $city?->id,
                'created_by' => $user->id,
                'is_active' => true,
                'is_verify' => true,
            ],
            [
                'name' => 'Bureau Open Space Executive',
                'description' => 'Plateau de bureaux lumineux idéal pour startup ou PME.',
                'price' => 900000,
                'price_type' => 'monthly',
                'surface' => 130,
                'rooms' => 3,
                'address' => '24 Avenue Colonel Lukusa',
                'location' => 'Gombe, Kinshasa',
                'phone' => '+243 821 000 222',
                'email' => 'executive@bureau.cd',
                'active' => true,
                'city_id' => $city?->id,
                'created_by' => $user->id,
                'is_active' => true,
                'is_verify' => true,
            ],
            [
                'name' => 'Bureau Professionnel Kintambo',
                'description' => 'Bureau individuel au calme pour professions libérales ou consultants.',
                'price' => 400000,
                'price_type' => 'monthly',
                'surface' => 45,
                'rooms' => 2,
                'address' => '15 Avenue Kasa-Vubu',
                'location' => 'Kintambo, Kinshasa',
                'phone' => '+243 821 000 333',
                'email' => 'kintambo@bureau.cd',
                'active' => true,
                'city_id' => $city?->id,
                'created_by' => $user->id,
                'is_active' => true,
                'is_verify' => true,
            ],
            [
                'name' => 'Bureau d\'Affaires Lubumbashi',
                'description' => 'Bureau fonctionnel en centre-ville de Lubumbashi.',
                'price' => 750000,
                'price_type' => 'monthly',
                'surface' => 90,
                'rooms' => 3,
                'address' => '5 Avenue du 30 Juin',
                'location' => 'Lubumbashi',
                'phone' => '+243 821 000 444',
                'email' => 'lubumbashi@bureau.cd',
                'active' => true,
                'city_id' => $city?->id,
                'created_by' => $user->id,
                'is_active' => true,
                'is_verify' => true,
            ],
            [
                'name' => 'Espace Co-working & Bureau Privé',
                'description' => 'Espace de travail moderne avec accès fibre optique et groupe électrogène.',
                'price' => 600000,
                'price_type' => 'monthly',
                'surface' => 70,
                'rooms' => 2,
                'address' => '8 Avenue de la Justice',
                'location' => 'Gombe, Kinshasa',
                'phone' => '+243 821 000 555',
                'email' => 'coworking@bureau.cd',
                'active' => true,
                'city_id' => $city?->id,
                'created_by' => $user->id,
                'is_active' => true,
                'is_verify' => true,
            ],
        ];

        foreach ($bureaux as $bureau) {
            $b = Bureau::create($bureau);
            $b->images()->create([
                'image_url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
                'cover_image' => true,
            ]);
        }
    }
}
