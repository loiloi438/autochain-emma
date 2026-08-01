<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockchainTx extends Model
{
    protected $table = 'blockchain_txs';

    protected $fillable = ['tx_hash', 'action', 'vehicle_id', 'user_id', 'status', 'payload', 'confirmed_at'];
    protected function casts(): array { return ['payload' => 'array', 'confirmed_at' => 'datetime']; }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function user() { return $this->belongsTo(User::class); }
}
