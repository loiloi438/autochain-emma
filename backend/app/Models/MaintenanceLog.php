<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    protected $fillable = ['vehicle_id', 'garage_id', 'service_type', 'description', 'parts_hash', 'cost', 'tx_hash', 'performed_at'];
    protected function casts(): array { return ['performed_at' => 'datetime', 'cost' => 'decimal:2']; }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function garage() { return $this->belongsTo(User::class, 'garage_id'); }
}
