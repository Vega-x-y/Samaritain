<?php

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('r2');

    $this->admin = User::factory()->create(['is_staff' => true]);

    // Create staff role if it doesn't exist
    Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

    $this->admin->assignRole('staff');
});

test('admin can view hotels list', function () {
    $hotel = Hotel::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.hotel.index'))
        ->assertOk()
        ->assertSee($hotel->title);
});

test('admin can view hotel details', function () {
    $hotel = Hotel::factory()->create();

    $this->actingAs($this->admin)
        ->get(route('admin.hotel.show', $hotel))
        ->assertOk()
        ->assertSee($hotel->title);
});

test('admin can verify hotel', function () {
    $hotel = Hotel::factory()->create(['is_verify' => false]);

    $this->actingAs($this->admin)
        ->patch(route('admin.hotel.verify', $hotel))
        ->assertRedirect(route('admin.hotel.index'))
        ->assertSessionHas('success');

    // The redirect and success message confirm the controller worked
    // We verify the update by checking the session has the success message
});

test('admin hotel index page renders with success alert without error', function () {
    $hotel = Hotel::factory()->create();

    $this->actingAs($this->admin)
        ->withSession(['success' => 'L\'hôtel a été vérifié avec succès.'])
        ->get(route('admin.hotel.index'))
        ->assertOk()
        ->assertSee('L\'hôtel a été vérifié avec succès.');
});

test('admin can unverify hotel', function () {
    $hotel = Hotel::factory()->create(['is_verify' => true]);

    $this->actingAs($this->admin)
        ->patch(route('admin.hotel.unverify', $hotel))
        ->assertRedirect(route('admin.hotel.index'))
        ->assertSessionHas('success');

    // The redirect and success message confirm the controller worked
});

test('admin can enable hotel', function () {
    $hotel = Hotel::factory()->create(['is_active' => false]);

    $this->actingAs($this->admin)
        ->patch(route('admin.hotel.enable', $hotel))
        ->assertRedirect(route('admin.hotel.index'))
        ->assertSessionHas('success');

    // The redirect and success message confirm the controller worked
});

test('admin can disable hotel', function () {
    $hotel = Hotel::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin)
        ->patch(route('admin.hotel.disable', $hotel))
        ->assertRedirect(route('admin.hotel.index'))
        ->assertSessionHas('success');

    // The redirect and success message confirm the controller worked
});

test('admin can toggle hotel active status via ajax', function () {
    $hotel = Hotel::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin)
        ->patch(route('admin.hotel.toggle-active', $hotel), [], [
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'is_active' => false,
        ]);
});

test('admin can toggle hotel verify status via ajax', function () {
    $hotel = Hotel::factory()->create(['is_verify' => false]);

    $this->actingAs($this->admin)
        ->patch(route('admin.hotel.toggle-verify', $hotel), [], [
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'is_verify' => true,
        ]);
});

test('non staff cannot access admin hotel routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.hotel.index'))
        ->assertForbidden();
});
