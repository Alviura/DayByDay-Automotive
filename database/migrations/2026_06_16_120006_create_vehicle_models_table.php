<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_make_id')->constrained('vehicle_makes')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['vehicle_make_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_models');
    }
};
