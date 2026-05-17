<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            // Region IX
            ['name' => 'Zamboanga',     'province' => 'Zamboanga City',     'region' => 'Region IX'],

            // Region X
            ['name' => 'Cagayan de Oro','province' => 'Misamis Oriental',   'region' => 'Region X'],
            ['name' => 'Iligan',        'province' => 'Lanao del Norte',    'region' => 'Region X'],

            // Region XI
            ['name' => 'Davao City',    'province' => 'Davao del Sur',      'region' => 'Region XI'],
            ['name' => 'Tagum',         'province' => 'Davao del Norte',    'region' => 'Region XI'],
            ['name' => 'Digos',         'province' => 'Davao del Sur',      'region' => 'Region XI'],

            // Region XII
            ['name' => 'General Santos','province' => 'South Cotabato',     'region' => 'Region XII'],
            ['name' => 'Koronadal',     'province' => 'South Cotabato',     'region' => 'Region XII'],
            ['name' => 'Polomolok',     'province' => 'South Cotabato',     'region' => 'Region XII'],
            
            // Caraga
            ['name' => 'Butuan',        'province' => 'Agusan del Norte',   'region' => 'Caraga'],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['name' => $city['name'], 'province' => $city['province']],
                array_merge($city, ['status' => 'active'])
            );
        }
    }
}