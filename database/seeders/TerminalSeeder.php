<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Terminal;
use Illuminate\Database\Seeder;

class TerminalSeeder extends Seeder
{
    public function run(): void
    {
        $terminals = [
            // Mindanao
            ['city' => 'Davao City',    'name' => 'Davao Ecoland Bus Terminal',    'code' => 'DVO',  'address' => 'Ecoland Drive, Davao City',                          'lat' => 7.0897,   'lng' => 125.6145],
            ['city' => 'Cagayan de Oro','name' => 'CDO Agora Bus Terminal',        'code' => 'CDO',  'address' => 'Mortola St., Cagayan de Oro City',                   'lat' => 8.4776,   'lng' => 124.6500],
            ['city' => 'General Santos','name' => 'GenSan Bus Terminal',           'code' => 'GEN',  'address' => 'Santiago Blvd., General Santos City',                'lat' => 6.1164,   'lng' => 125.1716],
            ['city' => 'Zamboanga',     'name' => 'Zamboanga Bus Terminal',        'code' => 'ZAM',  'address' => 'Veterans Ave., Zamboanga City',                      'lat' => 6.9101,   'lng' => 122.0730],
            ['city' => 'Iligan',        'name' => 'Iligan Bus Terminal',           'code' => 'ILI',  'address' => 'Tambacan, Iligan City',                              'lat' => 8.2280,   'lng' => 124.2452],
            ['city' => 'Tagum',         'name' => 'Tagum City Terminal',           'code' => 'TGM',  'address' => 'Tagum City, Davao del Norte',                        'lat' => 7.4475,   'lng' => 125.8080],
            ['city' => 'Koronadal',     'name' => 'Koronadal Transport Terminal',  'code' => 'KOR',  'address' => 'Koronadal City, South Cotabato',                     'lat' => 6.4975,   'lng' => 124.8472],
            ['city' => 'Digos',         'name' => 'Digos City Bus Terminal',       'code' => 'DIG',  'address' => 'Rizal Avenue, Digos City',                           'lat' => 6.7496,   'lng' => 125.3557],
            ['city' => 'Butuan',        'name' => 'Butuan Integrated Terminal',    'code' => 'BXU',  'address' => 'Ampayon, Butuan City',                               'lat' => 8.9482,   'lng' => 125.5684],
        ];

        foreach ($terminals as $data) {
            $city = City::where('name', $data['city'])->first();

            if (! $city) {
                $this->command->warn("TerminalSeeder: city '{$data['city']}' not found — skipping {$data['name']}.");
                continue;
            }

            Terminal::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name'           => $data['name'],
                    'city_id'        => $city->id,
                    'address'        => $data['address'],
                    'latitude'       => $data['lat'],
                    'longitude'      => $data['lng'],
                    'opening_time'   => '04:00:00',
                    'closing_time'   => '23:59:00',
                    'status'         => 'active',
                ]
            );
        }

        // Wire terminals back to routes (origin + destination)
        $this->linkTerminalsToRoutes();
    }

    private function linkTerminalsToRoutes(): void
    {
        \App\Models\BusRoute::with(['originCity', 'destinationCity'])->get()
            ->each(function ($route) {
                $origin = Terminal::whereHas('city', fn ($q) =>
                    $q->where('id', $route->origin_city_id)
                )->first();

                $dest = Terminal::whereHas('city', fn ($q) =>
                    $q->where('id', $route->destination_city_id)
                )->first();

                $route->update([
                    'origin_terminal_id'      => $origin?->id,
                    'destination_terminal_id' => $dest?->id,
                ]);
            });
    }
}