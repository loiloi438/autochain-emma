<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('liters', 8, 2);
            $table->decimal('amount', 10, 2)->nullable();
            $table->unsignedBigInteger('km');
            $table->timestamp('filled_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('fuel_logs'); }
};
