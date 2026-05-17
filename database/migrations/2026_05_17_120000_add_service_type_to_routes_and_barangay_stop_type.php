<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('routes', 'service_type')) {
            Schema::table('routes', function (Blueprint $table) {
                $table->enum('service_type', ['non_stop', 'express', 'regular'])
                    ->default('regular')
                    ->after('destination_city_id');
            });

            DB::table('routes')->whereNull('service_type')->update(['service_type' => 'regular']);
        }

        // Allow multiple routes per corridor (Non-Stop / Express / Regular)
        $indexes = collect(DB::select('SHOW INDEX FROM routes'))
            ->pluck('Key_name')
            ->unique();

        if ($indexes->contains('routes_route_name_unique')) {
            Schema::table('routes', function (Blueprint $table) {
                $table->dropUnique('routes_route_name_unique');
            });
        }

        if (! $indexes->contains('routes_origin_city_id_destination_city_id_service_type_unique')) {
            Schema::table('routes', function (Blueprint $table) {
                $table->unique(
                    ['origin_city_id', 'destination_city_id', 'service_type'],
                    'routes_origin_dest_service_unique'
                );
            });
        }

        if (Schema::hasTable('stops') && Schema::hasColumn('stops', 'type')) {
            DB::statement(
                "ALTER TABLE stops MODIFY COLUMN type ENUM('terminal', 'pickup', 'dropoff', 'waypoint', 'barangay') NOT NULL DEFAULT 'pickup'"
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('routes', 'service_type')) {
            Schema::table('routes', function (Blueprint $table) {
                $table->dropUnique('routes_origin_dest_service_unique');
                $table->dropColumn('service_type');
                $table->unique('route_name');
            });
        }

        if (Schema::hasTable('stops') && Schema::hasColumn('stops', 'type')) {
            DB::statement(
                "ALTER TABLE stops MODIFY COLUMN type ENUM('terminal', 'pickup', 'dropoff', 'waypoint') NOT NULL DEFAULT 'pickup'"
            );
        }
    }
};
