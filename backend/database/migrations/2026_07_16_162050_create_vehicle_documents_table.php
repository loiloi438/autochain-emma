<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicle_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_type');
            $table->string('title');
            $table->string('path');
            $table->string('sha256_hash', 64)->index();
            $table->string('ipfs_cid')->nullable();
            $table->string('tx_hash')->nullable()->index();
            $table->date('expires_at')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('vehicle_documents'); }
};
