<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->unique();
            $table->string('vin')->nullable();
            $table->string('vin_hash', 66)->unique();
            $table->string('brand');
            $table->string('model');
            $table->unsignedSmallInteger('year')->nullable();
            $table->enum('status', ['available', 'assigned', 'maintenance', 'broken', 'archived'])->default('available');
            $table->unsignedBigInteger('current_km')->default(0);
            $table->date('technical_inspection_due_at')->nullable();
            $table->date('insurance_due_at')->nullable();
            $table->unsignedBigInteger('oil_change_due_km')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('vehicles'); }
};
