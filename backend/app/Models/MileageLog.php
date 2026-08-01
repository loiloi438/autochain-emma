<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MileageLog extends Model
{
    protected $fillable = ['vehicle_id', 'user_id', 'km', 'source', 'tx_hash', 'recorded_at', 'note'];
    protected function casts(): array { return ['recorded_at' => 'datetime']; }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function user() { return $this->belongsTo(User::class); }
}
