<?php

namespace Database\Seeders;

use App\Models\Boutique;
use App\Models\City;
use App\Models\User;
use Illuminate\Database\Seeder;

class BoutiqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();
        $city = City::first();

        $boutiques = [
            [
                'name' => 'Boutique du Centre-Ville',
                'description' => 'Boutique principale située au cœur du centre-ville, idéale pour commerce de détail.',
                'location' => 'Avenue Principale, Centre-Ville',
                'price' => 500000,
                'price_type' => 'monthly',
                'surface' => 85,
                'rooms' => 2,
                'address' => '12 Avenue de la Paix',
                'phone' => '+243 812 345 678',
                'email' => 'centre-ville@boutique.cd',
                'active' => true,
                'city_id' => $city?->id,
                'created_by' => $user->id,
                'is_active' => true,
                'is_verify' => true,
            ],
            [
                'name' => 'Boutique Gombe Premium',
                'description' => 'Boutique moderne et spacieuse dans le quartier d\'affaires Gombe.',
                'location' => 'Gombe, Kinshasa',
                'price' => 1200000,
                'price_type' => 'monthly',
                'surface' => 120,
                'rooms' => 3,
                'address' => '45 Boulevard du 30 Juin',
                'phone' => '+243 813 456 789',
                'email' => 'gombe@boutique.cd',
                'active' => true,
                'city_id' => $city?->id,
                'created_by' => $user->id,
                'is_active' => true,
                'is_verify' => true,
            ],
            [
                'name' => 'Boutique Commerciale Matete',
                'description' => 'Emplacement commercial à fort passage, parfait pour supérette ou prêt-à-porter.',
                'location' => 'Matete, Kinshasa',
                'price' => 350000,
                'price_type' => 'monthly',
                'surface' => 60,
                'rooms' => 1,
                'address' => '88 Rue du Marché',
                'phone' => '+243 814 567 890',
                'email' => 'matete@boutique.cd',
                'active' => true,
                'city_id' => $city?->id,
                'created_by' => $user->id,
                'is_active' => true,
                'is_verify' => true,
            ],
            [
                'name' => 'Boutique Galerie Lubumbashi',
                'description' => 'Local commercial dans une galerie marchande sécurisée.',
                'location' => 'Lubumbashi',
                'price' => 800000,
                'price_type' => 'monthly',
                'surface' => 95,
                'rooms' => 2,
                'address' => '15 Avenue de l\'Indépendance',
                'phone' => '+243 815 678 901',
                'email' => 'lubumbashi@boutique.cd',
                'active' => true,
                'city_id' => $city?->id,
                'created_by' => $user->id,
                'is_active' => true,
                'is_verify' => true,
            ],
        ];

        foreach ($boutiques as $boutique) {
            $b = Boutique::create($boutique);
            $b->images()->create([
                'image_url' => 'https://images.unsplash.com/photo-1556740738-b6a63e27c4df?auto=format&fit=crop&w=800&q=80',
                'cover_image' => true,
            ]);
        }
    }
}
