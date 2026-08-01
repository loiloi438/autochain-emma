<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = ['vehicle_id', 'type', 'title', 'message', 'due_at', 'status'];
    protected function casts(): array { return ['due_at' => 'date']; }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
}
