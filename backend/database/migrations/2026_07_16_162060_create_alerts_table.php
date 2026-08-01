<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message')->nullable();
            $table->date('due_at')->nullable();
            $table->enum('status', ['open', 'done', 'dismissed'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('alerts'); }
};
