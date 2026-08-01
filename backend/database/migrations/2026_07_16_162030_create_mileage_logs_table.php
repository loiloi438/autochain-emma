<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mileage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('km');
            $table->enum('source', ['backend', 'blockchain'])->default('backend');
            $table->string('tx_hash')->nullable()->index();
            $table->timestamp('recorded_at')->useCurrent();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('mileage_logs'); }
};
