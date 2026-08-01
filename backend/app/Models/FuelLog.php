<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelLog extends Model
{
    protected $fillable = ['vehicle_id', 'user_id', 'liters', 'amount', 'km', 'filled_at'];
    protected function casts(): array { return ['filled_at' => 'datetime', 'liters' => 'decimal:2', 'amount' => 'decimal:2']; }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function user() { return $this->belongsTo(User::class); }
}
