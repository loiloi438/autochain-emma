<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Assignment;
use App\Models\MaintenanceLog;
use App\Models\MileageLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin' => 'Super Admin',
            'manager' => 'Gestionnaire de Parc',
            'driver' => 'Chauffeur',
            'garage' => 'Garagiste Agréé',
            'auditor' => 'Auditeur',
        ];

        foreach (array_keys($roles) as $role) {
            Role::findOrCreate($role, 'web');
        }

        $users = [
            ['admin', 'Admin AutoChain', 'admin@autochain.test', '0xf39fd6e51aad88f6f4ce6ab8827279cfffb92266'],
            ['manager', 'Gestionnaire Parc', 'gestionnaire@autochain.test', '0x70997970c51812dc3a010c7d01b50e0d17dc79c8'],
            ['driver', 'Chauffeur Demo', 'chauffeur@autochain.test', '0x3c44cdddb6a900fa2b585dd299e03d12fa4293bc'],
            ['garage', 'Garage Certifié', 'garage@autochain.test', '0x90f79bf6eb2c4f870365e785982e1f101e93b906'],
            ['auditor', 'Auditeur Public', 'auditeur@autochain.test', '0x15d34aaf54267db7d7c367839aaf71a00a2c6a65'],
        ];

        foreach ($users as [$role, $name, $email, $wallet]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('password'), 'wallet_address' => strtolower($wallet)]
            );
            $user->syncRoles([$role]);
        }

        $manager = User::where('email', 'gestionnaire@autochain.test')->first();
        $driver = User::where('email', 'chauffeur@autochain.test')->first();
        $garage = User::where('email', 'garage@autochain.test')->first();

        $vehicle = Vehicle::updateOrCreate(
            ['plate_number' => 'AC-001-EM'],
            [
                'vin' => 'VF1AUT0CHAIN001',
                'vin_hash' => '0x'.hash('sha256', 'VF1AUT0CHAIN001'),
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'year' => 2021,
                'status' => 'assigned',
                'current_km' => 45200,
                'technical_inspection_due_at' => now()->addDays(25),
                'insurance_due_at' => now()->addDays(45),
                'oil_change_due_km' => 45500,
            ]
        );

        Assignment::firstOrCreate(
            ['vehicle_id' => $vehicle->id, 'user_id' => $driver->id, 'status' => 'active'],
            ['assigned_by' => $manager->id]
        );

        MileageLog::firstOrCreate(
            ['vehicle_id' => $vehicle->id, 'km' => 45200],
            ['user_id' => $driver->id, 'source' => 'blockchain', 'tx_hash' => '0xdemoMileageTx']
        );

        MaintenanceLog::firstOrCreate(
            ['vehicle_id' => $vehicle->id, 'service_type' => 'Vidange'],
            [
                'garage_id' => $garage->id,
                'description' => 'Vidange moteur certifiée',
                'parts_hash' => '0x'.hash('sha256', 'huile-filtre'),
                'cost' => 95000,
                'tx_hash' => '0xdemoMaintenanceTx',
            ]
        );

        Alert::firstOrCreate(
            ['vehicle_id' => $vehicle->id, 'type' => 'technical_inspection', 'status' => 'open'],
            ['title' => 'Contrôle technique à prévoir', 'due_at' => $vehicle->technical_inspection_due_at]
        );
    }
}
