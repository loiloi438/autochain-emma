<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use App\Models\Vehicle;
use App\Models\User;
use Spatie\Permission\Models\Role as SpatieRole;

class BlockchainControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite'])->run();
        $this->createBlockchainArtifact();
    }

    protected function createBlockchainArtifact(array $roles = null): void
    {
        $blockchainPath = storage_path('app/blockchain');
        if (! File::exists($blockchainPath)) {
            File::makeDirectory($blockchainPath, 0755, true);
        }

        $artifact = [
            'address' => '0x1234567890abcdef',
            'roles' => $roles ?? [
                'Manager' => '0xabc123',
                'Driver' => '0xdef456',
                'Garage' => '0xfeedbeef',
            ],
        ];

        File::put($blockchainPath . '/VehicleRegistry.json', json_encode($artifact));
    }

    public function test_contract_info_returns_address_and_artifact()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/blockchain/contract');

        $response->assertStatus(200)
            ->assertJsonStructure(['address', 'artifact'])
            ->assertJson(['address' => '0x1234567890abcdef']);

        $this->assertArrayHasKey('roles', $response->json('artifact'));
    }

    public function test_sync_roles_assigns_roles_for_mapped_wallet_addresses()
    {
        $manager = User::factory()->create(['wallet_address' => '0xabc123']);
        $driver = User::factory()->create(['wallet_address' => '0xdef456']);

        $response = $this->actingAs($manager)->postJson('/api/blockchain/sync-roles');

        $response->assertStatus(200)
            ->assertJson(['synced' => true])
            ->assertJsonPath('mapping.manager', $manager->id)
            ->assertJsonPath('mapping.driver', $driver->id)
            ->assertJsonPath('mapping.garage', null);

        $this->assertTrue($manager->fresh()->hasRole('manager'));
        $this->assertTrue($driver->fresh()->hasRole('driver'));
        $this->assertFalse($driver->hasRole('garage'));

        $this->assertDatabaseHas('roles', ['name' => 'manager']);
        $this->assertDatabaseHas('roles', ['name' => 'driver']);
        $this->assertDatabaseHas('roles', ['name' => 'garage']);
    }

    public function test_sync_tx_pending_does_not_apply_blockchain_action()
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::create(['plate_number' => 'P1', 'brand' => 'B', 'model' => 'M', 'current_km' => 0]);

        $payload = [
            'tx_hash' => '0xpending123',
            'action' => 'mileage',
            'vehicle_id' => $vehicle->id,
            'status' => 'pending',
            'payload' => ['km' => 250],
        ];

        $response = $this->actingAs($user)->postJson('/api/blockchain/txs', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseMissing('mileage_logs', ['tx_hash' => '0xpending123']);
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'current_km' => 0]);
    }
}
