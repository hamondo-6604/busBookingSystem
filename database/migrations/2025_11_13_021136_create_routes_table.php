<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');     // e.g. "Davao City → General Santos (Express)"

            // Link to cities table properly
            $table->foreignId('origin_city_id')
                ->constrained('cities')
                ->onDelete('restrict');

            $table->foreignId('destination_city_id')
                ->constrained('cities')
                ->onDelete('restrict');

            // non_stop = 0 intermediate stops | express = few major stops | regular = many barangay/city stops
            $table->enum('service_type', ['non_stop', 'express', 'regular'])
                ->default('regular');

            $table->integer('distance_km')->nullable();
            $table->integer('estimated_duration_minutes')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['origin_city_id', 'destination_city_id', 'service_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};