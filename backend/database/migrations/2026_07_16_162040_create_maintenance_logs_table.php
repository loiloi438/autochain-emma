<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('garage_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('service_type');
            $table->text('description')->nullable();
            $table->string('parts_hash', 66)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('tx_hash')->nullable()->index();
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('maintenance_logs'); }
};
