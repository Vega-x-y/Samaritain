<?php

use App\Models\Boutique;
use App\Models\Bureau;
use Database\Seeders\BoutiqueSeeder;
use Database\Seeders\BureauSeeder;

test('boutique seeder creates boutiques', function () {
    // Seed only the BoutiqueSeeder
    $this->seed(BoutiqueSeeder::class);

    // Assert that boutiques were created
    $this->assertDatabaseCount('boutiques', 4);

    // Check specific boutique
    $this->assertDatabaseHas('boutiques', [
        'name' => 'Boutique du Centre-Ville',
        'active' => true,
        'is_active' => true,
    ]);
});

test('bureau seeder creates bureaus', function () {
    // Seed only the BureauSeeder
    $this->seed(BureauSeeder::class);

    // Assert that bureaus were created
    $this->assertDatabaseCount('bureaus', 5);

    // Check specific bureau
    $this->assertDatabaseHas('bureaus', [
        'name' => 'Siège Social',
        'active' => true,
        'name' => 'Siège Social Corporate',
        'is_active' => true,
    ]);
});
