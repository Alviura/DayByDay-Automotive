<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additional vehicle-model compatibility for a product, beyond its primary
     * make/model. Supports parts that fit several models (e.g. one brake pad
     * fitting Toyota Premio and Allion).
     */
    public function up(): void
    {
        Schema::create('product_vehicle_model', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('vehicle_model_id')->constrained('vehicle_models')->cascadeOnDelete();

            $table->unique(['product_id', 'vehicle_model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_vehicle_model');
    }
};
