<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master list of named stops (bus stops, landmarks, waypoints)
        Schema::create('stops', function (Blueprint $table) {
            $table->id();

            $table->string('name');                     // "Ulas", "Toril", "Digos", "Davao Ecoland"
            $table->string('code')->unique()->nullable(); // "DVO-ULAS"

            $table->foreignId('city_id')
                ->nullable()
                ->constrained('cities')
                ->onDelete('set null');

            // Optional: link to a terminal if this stop IS a terminal
            $table->foreignId('terminal_id')
                ->nullable()
                ->constrained('terminals')
                ->onDelete('set null');

            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->enum('type', ['terminal', 'pickup', 'dropoff', 'waypoint', 'barangay'])
                ->default('pickup');

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Pivot: which stops does a route pass through, and in what order
        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();

            $table->foreignId('route_id')
                ->constrained('routes')
                ->onDelete('cascade');

            $table->foreignId('stop_id')
                ->constrained('stops')
                ->onDelete('cascade');

            // 1 = first stop after origin, 2 = second, etc.
            // origin itself is stop_order = 0, destination is the final
            $table->unsignedSmallInteger('stop_order');

            // How many minutes from departure does the bus reach this stop
            $table->integer('minutes_from_origin')->nullable();

            // Extra fare to board at this stop (partial route pricing)
            $table->decimal('fare_from_origin', 10, 2)->nullable();

            // Is this stop available for passenger pickup/dropoff or just a waypoint
            $table->boolean('allows_boarding')->default(true);
            $table->boolean('allows_alighting')->default(true);

            $table->timestamps();

            $table->unique(['route_id', 'stop_id']);
            $table->index(['route_id', 'stop_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_stops');
        Schema::dropIfExists('stops');
    }
};