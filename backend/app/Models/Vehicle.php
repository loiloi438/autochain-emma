<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'plate_number', 'vin', 'vin_hash', 'brand', 'model', 'year', 'status',
        'current_km', 'technical_inspection_due_at', 'insurance_due_at',
        'oil_change_due_km', 'meta',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $vehicle) {
            if (empty($vehicle->vin_hash)) {
                $source = $vehicle->vin ?: $vehicle->plate_number;
                $vehicle->vin_hash = '0x'.hash('sha256', $source);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'technical_inspection_due_at' => 'date',
            'insurance_due_at' => 'date',
        ];
    }

    public function assignments(): HasMany { return $this->hasMany(Assignment::class); }
    public function mileageLogs(): HasMany { return $this->hasMany(MileageLog::class); }
    public function maintenanceLogs(): HasMany { return $this->hasMany(MaintenanceLog::class); }
    public function documents(): HasMany { return $this->hasMany(VehicleDocument::class); }
    public function alerts(): HasMany { return $this->hasMany(Alert::class); }
    public function fuelLogs(): HasMany { return $this->hasMany(FuelLog::class); }
    public function blockchainTxs(): HasMany { return $this->hasMany(BlockchainTx::class); }
}
