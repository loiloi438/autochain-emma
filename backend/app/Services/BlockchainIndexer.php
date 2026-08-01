<?php

namespace App\Services;

use App\Models\BlockchainTx;
use App\Models\MileageLog;
use App\Models\MaintenanceLog;
use App\Models\VehicleDocument;
use App\Models\Vehicle;
use Illuminate\Support\Facades\File;

class BlockchainIndexer
{
    public function artifact(): array
    {
        $path = storage_path('app/blockchain/VehicleRegistry.json');
        if (! File::exists($path)) {
            return [];
        }

        return json_decode(File::get($path), true) ?: [];
    }

    public function contractAddress(): ?string
    {
        return $this->artifact()['address'] ?? null;
    }

    /**
     * Retourne la map des roles déclarés dans l'artifact (roleName => address)
     *
     * @return array<string,string>
     */
    public function roles(): array
    {
        return $this->artifact()['roles'] ?? [];
    }

    public function sync(array $payload): BlockchainTx
    {
        return BlockchainTx::updateOrCreate(
            ['tx_hash' => $payload['tx_hash']],
            [
                'action' => $payload['action'],
                'vehicle_id' => $payload['vehicle_id'] ?? null,
                'user_id' => $payload['user_id'] ?? null,
                'status' => $payload['status'] ?? 'pending',
                'payload' => $payload['payload'] ?? null,
                'confirmed_at' => ($payload['status'] ?? null) === 'confirmed' ? now() : null,
            ]
        );
    }

    /**
     * Sync and apply a blockchain tx payload — idempotent for 'confirmed' status.
     *
     * @param array $payload
     * @return BlockchainTx
     */
    public function syncAndApply(array $payload): BlockchainTx
    {
        $tx = $this->sync($payload);

        // Only apply when confirmed
        if (($tx->status ?? null) !== 'confirmed') {
            return $tx;
        }

        $action = $tx->action;
        $vehicleId = $tx->vehicle_id;
        $pl = $tx->payload ?? [];

        // Mileage: create MileageLog if not exists, update vehicle km
        if ($action === 'mileage' && $vehicleId && isset($pl['km'])) {
            if (! MileageLog::where('tx_hash', $tx->tx_hash)->exists()) {
                MileageLog::create([
                    'vehicle_id' => $vehicleId,
                    'user_id' => $tx->user_id,
                    'km' => $pl['km'],
                    'source' => 'blockchain',
                    'tx_hash' => $tx->tx_hash,
                    'note' => $pl['note'] ?? null,
                    'recorded_at' => now(),
                ]);

                $vehicle = Vehicle::find($vehicleId);
                if ($vehicle && $pl['km'] > $vehicle->current_km) {
                    $vehicle->update(['current_km' => $pl['km']]);
                }
            }
        }

        // Vehicle registration: keep on-chain registration metadata
        if ($action === 'registerVehicle' && $vehicleId && isset($pl['vin_hash'])) {
            $vehicle = Vehicle::find($vehicleId);
            if ($vehicle) {
                $meta = array_merge($vehicle->meta ?? [], ['onchain_vin_hash' => $pl['vin_hash']]);
                $vehicle->update(['meta' => $meta]);
            }
        }

        // Maintenance: create MaintenanceLog if not exists
        if (in_array($action, ['maintenance', 'recordMaintenance'], true) && $vehicleId && isset($pl['service_type'])) {
            if (! MaintenanceLog::where('tx_hash', $tx->tx_hash)->exists()) {
                MaintenanceLog::create([
                    'vehicle_id' => $vehicleId,
                    'garage_id' => $tx->user_id,
                    'service_type' => $pl['service_type'],
                    'description' => $pl['description'] ?? null,
                    'parts_hash' => $pl['parts_hash'] ?? null,
                    'cost' => $pl['cost'] ?? null,
                    'tx_hash' => $tx->tx_hash,
                    'performed_at' => now(),
                ]);
            }
        }

        // Document: create VehicleDocument if not exists
        if (in_array($action, ['document', 'recordDocumentHash'], true) && $vehicleId && isset($pl['doc_hash'])) {
            if (! VehicleDocument::where('tx_hash', $tx->tx_hash)->exists()) {
                VehicleDocument::create([
                    'vehicle_id' => $vehicleId,
                    'uploaded_by' => $tx->user_id,
                    'document_type' => $pl['doc_type'] ?? 'onchain',
                    'title' => $pl['title'] ?? 'Document certifié',
                    // On-chain documents do not have a stored path; use empty string to satisfy NOT NULL DB constraint
                    'path' => $pl['path'] ?? '',
                    'sha256_hash' => $pl['doc_hash'],
                    'ipfs_cid' => $pl['ipfs_cid'] ?? null,
                    'tx_hash' => $tx->tx_hash,
                    'expires_at' => $pl['expires_at'] ?? null,
                    'is_public' => isset($pl['ipfs_cid']),
                ]);
            }
        }

        return $tx;
    }
}
