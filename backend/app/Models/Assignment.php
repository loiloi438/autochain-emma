<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = ['vehicle_id', 'user_id', 'assigned_by', 'starts_at', 'ends_at', 'status', 'notes'];
    protected function casts(): array { return ['starts_at' => 'datetime', 'ends_at' => 'datetime']; }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function driver() { return $this->belongsTo(User::class, 'user_id'); }
    public function assigner() { return $this->belongsTo(User::class, 'assigned_by'); }
}
