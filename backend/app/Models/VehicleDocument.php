<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleDocument extends Model
{
    protected $fillable = ['vehicle_id', 'uploaded_by', 'document_type', 'title', 'path', 'sha256_hash', 'ipfs_cid', 'tx_hash', 'expires_at', 'is_public'];
    protected $table = 'vehicle_documents';
    protected function casts(): array { return ['expires_at' => 'date', 'is_public' => 'boolean']; }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
}
