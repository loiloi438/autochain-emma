<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;

class RoleGuardsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // run migrations
        $this->artisan('migrate', ['--database' => 'sqlite'])->run();
    }

    public function test_store_vehicle_requires_manager_or_admin()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/vehicles', [
            'plate_number' => 'TEST123', 'brand' => 'Make', 'model' => 'Model'
        ]);

        $response->assertStatus(403);

        // create manager role and assign
        SpatieRole::create(['name' => 'manager', 'guard_name' => 'web']);
        $user->assignRole('manager');

        $response = $this->actingAs($user)->postJson('/api/vehicles', [
            'plate_number' => 'TEST124', 'brand' => 'Make', 'model' => 'Model'
        ]);

        $response->assertStatus(201);
    }

    public function test_record_mileage_requires_driver_or_manager()
    {
        $user = User::factory()->create();
        $vehicle = \App\Models\Vehicle::create(['plate_number'=>'P1','brand'=>'B','model'=>'M','current_km'=>0]);

        $response = $this->actingAs($user)->postJson("/api/vehicles/{$vehicle->id}/mileage", ['km' => 100]);
        $response->assertStatus(403);

        SpatieRole::create(['name' => 'driver', 'guard_name' => 'web']);
        $user->assignRole('driver');

        $response = $this->actingAs($user)->postJson("/api/vehicles/{$vehicle->id}/mileage", ['km' => 100]);
        $response->assertStatus(201);
    }

    public function test_record_maintenance_requires_garage()
    {
        $user = User::factory()->create();
        $vehicle = \App\Models\Vehicle::create(['plate_number'=>'P2','brand'=>'B','model'=>'M','current_km'=>0]);

        $response = $this->actingAs($user)->postJson("/api/vehicles/{$vehicle->id}/maintenance", ['service_type' => 'Oil']);
        $response->assertStatus(403);

        SpatieRole::create(['name' => 'garage', 'guard_name' => 'web']);
        $user->assignRole('garage');

        $response = $this->actingAs($user)->postJson("/api/vehicles/{$vehicle->id}/maintenance", ['service_type' => 'Oil']);
        $response->assertStatus(201);
    }
}
