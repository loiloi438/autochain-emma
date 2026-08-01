<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blockchain_txs', function (Blueprint $table) {
            $table->id();
            $table->string('tx_hash')->unique();
            $table->string('action');
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'confirmed', 'failed'])->default('pending');
            $table->json('payload')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('blockchain_txs'); }
};
