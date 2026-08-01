<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Vehicle;
use Illuminate\Console\Command;

class GenerateFleetAlerts extends Command
{
    protected $signature = 'autochain:generate-alerts';

    protected $description = 'Genere les alertes CT, assurance et vidange pour la flotte';

    public function handle(): int
    {
        Vehicle::query()->each(function (Vehicle $vehicle) {
            if ($vehicle->technical_inspection_due_at && $vehicle->technical_inspection_due_at->diffInDays(now(), false) >= -30) {
                Alert::firstOrCreate(
                    ['vehicle_id' => $vehicle->id, 'type' => 'technical_inspection', 'status' => 'open'],
                    ['title' => 'Controle technique a prevoir', 'due_at' => $vehicle->technical_inspection_due_at]
                );
            }

            if ($vehicle->insurance_due_at && $vehicle->insurance_due_at->diffInDays(now(), false) >= -30) {
                Alert::firstOrCreate(
                    ['vehicle_id' => $vehicle->id, 'type' => 'insurance', 'status' => 'open'],
                    ['title' => 'Assurance a renouveler', 'due_at' => $vehicle->insurance_due_at]
                );
            }

            if ($vehicle->oil_change_due_km && $vehicle->current_km >= ($vehicle->oil_change_due_km - 500)) {
                Alert::firstOrCreate(
                    ['vehicle_id' => $vehicle->id, 'type' => 'oil_change', 'status' => 'open'],
                    ['title' => 'Vidange proche', 'message' => 'Seuil kilometrique bientot atteint.']
                );
            }
        });

        $this->info('Alertes flotte mises a jour.');

        return self::SUCCESS;
    }
}
