<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\BlockchainTx;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\MileageLog;
use App\Models\MaintenanceLog;
use App\Models\VehicleDocument;

class BlockchainSyncTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--database' => 'sqlite'])->run();
    }

    public function test_sync_and_apply_mileage_creates_log_and_updates_vehicle()
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::create(['plate_number' => 'V1', 'brand' => 'B', 'model' => 'M', 'current_km' => 0]);

        $payload = [
            'tx_hash' => '0xabc123',
            'action' => 'mileage',
            'vehicle_id' => $vehicle->id,
            'status' => 'confirmed',
            'payload' => ['km' => 500],
        ];

        $response = $this->actingAs($user)->postJson('/api/blockchain/txs', $payload);
        $response->assertStatus(201);

        $this->assertDatabaseHas('mileage_logs', ['vehicle_id' => $vehicle->id, 'km' => 500, 'tx_hash' => '0xabc123']);
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'current_km' => 500]);
    }

    public function test_sync_and_apply_maintenance_creates_record()
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::create(['plate_number' => 'V2', 'brand' => 'B', 'model' => 'M', 'current_km' => 0]);

        $payload = [
            'tx_hash' => '0xdef456',
            'action' => 'maintenance',
            'vehicle_id' => $vehicle->id,
            'status' => 'confirmed',
            'payload' => ['service_type' => 'Brake change', 'description' => 'Replaced pads'],
        ];

        $response = $this->actingAs($user)->postJson('/api/blockchain/txs', $payload);
        $response->assertStatus(201);

        $this->assertDatabaseHas('maintenance_logs', ['vehicle_id' => $vehicle->id, 'service_type' => 'Brake change', 'tx_hash' => '0xdef456']);
    }

    public function test_sync_and_apply_document_creates_document()
    {
        $user = User::factory()->create();
        $vehicle = Vehicle::create(['plate_number' => 'V3', 'brand' => 'B', 'model' => 'M', 'current_km' => 0]);

        $payload = [
            'tx_hash' => '0x789abc',
            'action' => 'document',
            'vehicle_id' => $vehicle->id,
            'status' => 'confirmed',
            'payload' => ['doc_hash' => '0xdeadbeef', 'doc_type' => 'insurance', 'ipfs_cid' => 'QmFakeCid'],
        ];

        $response = $this->actingAs($user)->postJson('/api/blockchain/txs', $payload);
        $response->assertStatus(201);

        $this->assertDatabaseHas('vehicle_documents', ['vehicle_id' => $vehicle->id, 'tx_hash' => '0x789abc', 'ipfs_cid' => 'QmFakeCid']);
    }

    public function test_public_history_reports_confirmed_and_pending_entries()
    {
        $vehicle = Vehicle::create(['plate_number' => 'V4', 'brand' => 'B', 'model' => 'M', 'current_km' => 0]);

        $user = \App\Models\User::factory()->create();

        MileageLog::create([
            'vehicle_id' => $vehicle->id,
            'user_id' => $user->id,
            'km' => 400,
            'source' => 'backend',
            'tx_hash' => '0xconfirmed',
            'recorded_at' => now(),
        ]);
        BlockchainTx::create([
            'tx_hash' => '0xconfirmed',
            'action' => 'mileage',
            'vehicle_id' => $vehicle->id,
            'status' => 'confirmed',
        ]);

        $garage = \App\Models\User::factory()->create();

        MaintenanceLog::create([
            'vehicle_id' => $vehicle->id,
            'garage_id' => $garage->id,
            'service_type' => 'Brake change',
            'description' => 'Pending maintenance',
            'tx_hash' => '0xpending',
            'performed_at' => now(),
        ]);
        BlockchainTx::create([
            'tx_hash' => '0xpending',
            'action' => 'maintenance',
            'vehicle_id' => $vehicle->id,
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/public/vehicles/'.$vehicle->id.'/history');

        $response->assertStatus(200)
            ->assertJsonPath('summary.total', 2)
            ->assertJsonPath('summary.certified', 1)
            ->assertJsonPath('summary.pending', 1)
            ->assertJsonCount(1, 'certified')
            ->assertJsonCount(1, 'pending');
    }
}
