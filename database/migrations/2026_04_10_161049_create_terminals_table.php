<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terminals', function (Blueprint $table) {
            $table->id();

            $table->string('name');                     // "Cubao Bus Terminal", "Pasay Terminal"
            $table->string('code')->unique()->nullable();// "CBT", "PSY" — short reference code

            $table->foreignId('city_id')
                ->constrained('cities')
                ->onDelete('restrict');

            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();

            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();

            $table->enum('status', ['active', 'inactive', 'under_construction'])
                ->default('active');

            $table->text('description')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Add origin_terminal_id and destination_terminal_id to routes
        Schema::table('routes', function (Blueprint $table) {
            $table->foreignId('origin_terminal_id')
                ->nullable()
                ->after('origin_city_id')
                ->constrained('terminals')
                ->onDelete('set null');

            $table->foreignId('destination_terminal_id')
                ->nullable()
                ->after('destination_city_id')
                ->constrained('terminals')
                ->onDelete('set null');
        });

        // Add departure/arrival terminal to trips
        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('departure_terminal_id')
                ->nullable()
                ->after('driver_id')
                ->constrained('terminals')
                ->onDelete('set null');

            $table->foreignId('arrival_terminal_id')
                ->nullable()
                ->after('departure_terminal_id')
                ->constrained('terminals')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['departure_terminal_id']);
            $table->dropForeign(['arrival_terminal_id']);
            $table->dropColumn(['departure_terminal_id', 'arrival_terminal_id']);
        });

        Schema::table('routes', function (Blueprint $table) {
            $table->dropForeign(['origin_terminal_id']);
            $table->dropForeign(['destination_terminal_id']);
            $table->dropColumn(['origin_terminal_id', 'destination_terminal_id']);
        });

        Schema::dropIfExists('terminals');
    }
};