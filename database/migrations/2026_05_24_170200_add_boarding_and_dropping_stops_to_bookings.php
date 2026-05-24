<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('boarding_stop_id')
                  ->nullable()
                  ->after('trip_id')
                  ->constrained('stops')
                  ->nullOnDelete();

            $table->foreignId('dropping_stop_id')
                  ->nullable()
                  ->after('boarding_stop_id')
                  ->constrained('stops')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('boarding_stop_id');
            $table->dropConstrainedForeignId('dropping_stop_id');
        });
    }
};
