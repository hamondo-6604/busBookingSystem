<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds round-trip support to the bookings table. Round-trip bookings are
     * stored as two linked Booking rows (one outbound + one return). The
     * outbound booking holds a foreign key (`return_booking_id`) pointing to
     * the return leg.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // 'one_way' for single-leg bookings, 'round_trip' for either leg
            // of a paired booking.
            $table->enum('trip_type', ['one_way', 'round_trip'])
                  ->default('one_way')
                  ->after('trip_id');

            // Set on the outbound booking only — points at the return Booking row.
            $table->foreignId('return_booking_id')
                  ->nullable()
                  ->after('trip_type')
                  ->constrained('bookings')
                  ->nullOnDelete();

            // True only on the return leg of a round-trip pair.
            $table->boolean('is_return_leg')
                  ->default(false)
                  ->after('return_booking_id');

            $table->index(['trip_type', 'is_return_leg']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['trip_type', 'is_return_leg']);
            $table->dropConstrainedForeignId('return_booking_id');
            $table->dropColumn(['trip_type', 'is_return_leg']);
        });
    }
};
