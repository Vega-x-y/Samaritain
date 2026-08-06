<?php

namespace Database\Seeders;

use App\Enums\ChantierStatus;
use App\Models\Artisan;
use App\Models\Facture;
use App\Models\MembreEquipe;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ArtisanDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create or get a demo user
        $user = User::firstOrCreate(
            ['email' => 'artisan@demo.com'],
            [
                'name' => 'Jean Dupont',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign artisan role
        $artisanRole = Role::firstOrCreate(['name' => 'artisan']);
        $user->assignRole($artisanRole);

        // 2. Create the artisan profile
        $artisan = Artisan::firstOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => 'Dupont Rénovation',
                'slug' => 'dupont-renovation',
                'profession' => 'Artisan Rénovateur',
                'bio' => 'Artisan passionné avec plus de 15 ans d\'expérience dans la rénovation intérieure et extérieure.',
                'phone' => '0612345678',
                'whatsapp' => '0612345678',
                'city' => 'Paris',
                'is_active' => true,
                'verified' => true,
            ]
        );

        // 3. Create clients
        $clients = [
            ['nom' => 'Marie Lambert', 'email' => 'marie.lambert@email.com', 'telephone' => '0611111111', 'adresse' => '15 Rue de la Paix, 75001 Paris'],
            ['nom' => 'Pierre Martin', 'email' => 'pierre.martin@email.com', 'telephone' => '0622222222', 'adresse' => '8 Avenue des Champs, 75008 Paris'],
            ['nom' => 'Sophie Bernard', 'email' => 'sophie.bernard@email.com', 'telephone' => '0633333333', 'adresse' => '24 Boulevard Haussmann, 75009 Paris'],
        ];

        $clientModels = [];
        foreach ($clients as $clientData) {
            $client = $artisan->clients()->firstOrCreate(
                ['email' => $clientData['email']],
                $clientData
            );
            $clientModels[] = $client;
        }

        // 4. Create chantiers (projects)
        $chantierData = [
            ['nom' => 'Rénovation cuisine', 'client_id' => $clientModels[0]->id, 'statut' => ChantierStatus::EN_COURS, 'budget' => 15000, 'date_debut' => '2026-06-01', 'type' => 'plomberie'],
            ['nom' => 'Salle de bain', 'client_id' => $clientModels[1]->id, 'statut' => ChantierStatus::TERMINE, 'budget' => 12000, 'date_debut' => '2026-05-15', 'date_fin' => '2026-07-01', 'type' => 'electricite'],
            ['nom' => 'Ravalement façade', 'client_id' => $clientModels[2]->id, 'statut' => ChantierStatus::DEVIS, 'budget' => 25000, 'date_debut' => '2026-08-01', 'type' => 'maconnerie'],
        ];

        $chantierModels = [];
        foreach ($chantierData as $data) {
            $chantier = $artisan->chantiers()->firstOrCreate(
                ['nom' => $data['nom']],
                [
                    'client_id' => $data['client_id'],
                    'statut' => $data['statut'],
                    'budget' => $data['budget'],
                    'date_debut' => $data['date_debut'],
                    'date_fin' => $data['date_fin'] ?? null,
                    'type' => $data['type'],
                ]
            );
            $chantierModels[] = $chantier;
        }

        // 5. Create invoices (factures)
        $now = now();
        for ($i = 0; $i < 6; $i++) {
            $month = $now->copy()->subMonths($i);
            $chantier = $chantierModels[array_rand($chantierModels)];
            Facture::firstOrCreate(
                [
                    'chantier_id' => $chantier->id,
                    'date_emission' => $month->format('Y-m-d'),
                ],
                [
                    'numero' => 'FAC-'.$month->format('Ym').'-'.$chantier->id,
                    'montant_ht' => rand(2000, 10000),
                    'montant_ttc' => rand(2400, 12000),
                    'statut' => 'payee',
                    'date_echeance' => $month->copy()->addDays(30)->format('Y-m-d'),
                ]
            );
        }

        // 6. Create team members (membres équipe)
        $membres = [
            ['nom' => 'Lucas Dubois', 'role' => 'Chef d\'équipe', 'telephone' => '0644444444', 'statut' => 'actif'],
            ['nom' => 'Antoine Petit', 'role' => 'Peintre', 'telephone' => '0655555555', 'statut' => 'actif'],
            ['nom' => 'Camille Moreau', 'role' => 'Apprenti', 'telephone' => '0666666666', 'statut' => 'actif'],
        ];

        foreach ($membres as $membreData) {
            MembreEquipe::firstOrCreate(
                ['artisan_id' => $artisan->id, 'nom' => $membreData['nom']],
                $membreData
            );
        }

        $this->command->info('✅ Données de démonstration créées avec succès !');
        $this->command->info('   Email: artisan@demo.com');
        $this->command->info('   Mot de passe: password');
    }
}
