<?php

namespace Database\Seeders;

use App\Models\BusRoute;
use App\Models\City;
use App\Models\Terminal;
use Illuminate\Database\Seeder;

class BusRouteSeeder extends Seeder
{
    public function run(): void
    {
        $routes = [
            // Davao → General Santos — three service variants (Mindanao Express)
            [
                'origin'       => 'Davao City',
                'destination'  => 'General Santos',
                'service_type' => BusRoute::SERVICE_NON_STOP,
                'distance'     => 145,
                'duration'     => 150,
                'description'  => 'Direct service. Davao Terminal → General Santos Terminal. 0 intermediate stops.',
            ],
            [
                'origin'       => 'Davao City',
                'destination'  => 'General Santos',
                'service_type' => BusRoute::SERVICE_EXPRESS,
                'distance'     => 145,
                'duration'     => 165,
                'description'  => 'Major city stops only (Toril, Digos, Koronadal). 3–4 intermediate stops.',
            ],
            [
                'origin'       => 'Davao City',
                'destination'  => 'General Santos',
                'service_type' => BusRoute::SERVICE_REGULAR,
                'distance'     => 145,
                'duration'     => 240,
                'description'  => 'Barangay and roadside pickups along the Davao–GenSan corridor. Many intermediate stops.',
            ],

            // Other Mindanao corridors (express by default)
            ['origin' => 'Davao City',    'destination' => 'Cagayan de Oro', 'service_type' => BusRoute::SERVICE_EXPRESS,  'distance' => 310, 'duration' => 360, 'description' => null],
            ['origin' => 'Cagayan de Oro','destination' => 'Iligan',         'service_type' => BusRoute::SERVICE_EXPRESS,  'distance' => 35,  'duration' => 60,  'description' => null],
            ['origin' => 'General Santos','destination' => 'Koronadal',      'service_type' => BusRoute::SERVICE_EXPRESS,  'distance' => 50,  'duration' => 75,  'description' => null],
            ['origin' => 'Davao City',    'destination' => 'Tagum',          'service_type' => BusRoute::SERVICE_REGULAR, 'distance' => 55,  'duration' => 80,  'description' => null],
            ['origin' => 'Cagayan de Oro','destination' => 'Butuan',         'service_type' => BusRoute::SERVICE_EXPRESS,  'distance' => 200, 'duration' => 240, 'description' => null],
            ['origin' => 'Davao City',    'destination' => 'Zamboanga',      'service_type' => BusRoute::SERVICE_NON_STOP, 'distance' => 500, 'duration' => 600, 'description' => null],
        ];

        foreach ($routes as $routeData) {
            $originCity = City::where('name', $routeData['origin'])->first();
            $destCity   = City::where('name', $routeData['destination'])->first();

            if (! $originCity || ! $destCity) {
                $this->command->warn(
                    "Skipping {$routeData['origin']} → {$routeData['destination']}: city not found."
                );
                continue;
            }

            $originTerminal = Terminal::where('city_id', $originCity->id)->first();
            $destTerminal   = Terminal::where('city_id', $destCity->id)->first();

            $serviceLabel = match ($routeData['service_type']) {
                BusRoute::SERVICE_NON_STOP => 'Non-Stop',
                BusRoute::SERVICE_EXPRESS  => 'Express',
                BusRoute::SERVICE_REGULAR  => 'Regular',
                default                    => ucfirst($routeData['service_type']),
            };

            BusRoute::updateOrCreate(
                [
                    'origin_city_id'      => $originCity->id,
                    'destination_city_id' => $destCity->id,
                    'service_type'        => $routeData['service_type'],
                ],
                [
                    'route_name'                 => "{$originCity->name} → {$destCity->name} ({$serviceLabel})",
                    'origin_terminal_id'         => $originTerminal?->id,
                    'destination_terminal_id'    => $destTerminal?->id,
                    'distance_km'                => $routeData['distance'],
                    'estimated_duration_minutes' => $routeData['duration'],
                    'status'                     => 'active',
                    'description'                => $routeData['description'],
                ]
            );
        }

        $this->command->info('BusRouteSeeder: seeded ' . BusRoute::count() . ' routes.');
    }
}
