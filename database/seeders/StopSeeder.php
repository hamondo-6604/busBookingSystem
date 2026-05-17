<?php

namespace Database\Seeders;

use App\Models\BusRoute;
use App\Models\City;
use App\Models\Stop;
use App\Models\Terminal;
use Illuminate\Database\Seeder;

class StopSeeder extends Seeder
{
    public function run(): void
    {
        $stops = [
            // ── Davao → General Santos corridor (terminals + barangays) ──
            ['city' => 'Davao City',     'terminal_code' => 'DVO',  'name' => 'Davao Ecoland',  'code' => 'DVO-ECO',  'type' => 'terminal'],
            ['city' => 'Davao City',     'terminal_code' => null,   'name' => 'Ulas',           'code' => 'DVO-ULAS', 'type' => 'barangay'],
            ['city' => 'Davao City',     'terminal_code' => null,   'name' => 'Mintal',         'code' => 'DVO-MINT', 'type' => 'barangay'],
            ['city' => 'Davao City',     'terminal_code' => null,   'name' => 'Toril',          'code' => 'DVO-TOR',  'type' => 'barangay'],
            ['city' => 'Davao City',     'terminal_code' => null,   'name' => 'Roxas',          'code' => 'DVO-ROX',  'type' => 'barangay'],
            ['city' => 'Digos',          'terminal_code' => 'DIG',  'name' => 'Digos',          'code' => 'DIG-TRM',  'type' => 'terminal'],
            ['city' => 'Digos',          'terminal_code' => null,   'name' => 'Sta. Cruz',      'code' => 'DIG-STCR', 'type' => 'barangay'],
            ['city' => 'Digos',          'terminal_code' => null,   'name' => 'Sulop',          'code' => 'DIG-SUL',  'type' => 'barangay'],
            ['city' => 'Digos',          'terminal_code' => null,   'name' => 'Malalag',        'code' => 'DIG-MAL',  'type' => 'barangay'],
            ['city' => 'Digos',          'terminal_code' => null,   'name' => 'Padada',         'code' => 'DIG-PAD',  'type' => 'barangay'],
            ['city' => 'Digos',          'terminal_code' => null,   'name' => 'Hagonoy',        'code' => 'DIG-HAG',  'type' => 'barangay'],
            ['city' => 'Digos',          'terminal_code' => null,   'name' => 'Matanao',        'code' => 'DIG-MAT',  'type' => 'barangay'],
            ['city' => 'Digos',          'terminal_code' => null,   'name' => 'Bansalan',       'code' => 'DIG-BAN',  'type' => 'barangay'],
            ['city' => 'Koronadal',      'terminal_code' => 'KOR',  'name' => 'Koronadal',      'code' => 'KOR-TRM',  'type' => 'terminal'],
            ['city' => 'Polomolok',      'terminal_code' => null,   'name' => 'Polomolok',      'code' => 'GEN-POL',  'type' => 'barangay'],
            ['city' => 'General Santos', 'terminal_code' => 'GEN',  'name' => 'General Santos', 'code' => 'GEN-TRM',  'type' => 'terminal'],

            // ── Other Mindanao routes ──
            ['city' => 'Davao City',     'terminal_code' => null,   'name' => 'Panacan',        'code' => 'PAN-STOP', 'type' => 'barangay'],
            ['city' => 'Cagayan de Oro', 'terminal_code' => 'CDO',  'name' => 'CDO Agora',      'code' => 'CDO-STOP', 'type' => 'terminal'],
            ['city' => 'Cagayan de Oro', 'terminal_code' => null,   'name' => 'Puerto',         'code' => 'PUE-STOP', 'type' => 'barangay'],
            ['city' => 'Tagum',          'terminal_code' => 'TGM',  'name' => 'Tagum',          'code' => 'TGM-STOP', 'type' => 'terminal'],
            ['city' => 'Butuan',         'terminal_code' => 'BXU',  'name' => 'Butuan',         'code' => 'BXU-STOP', 'type' => 'terminal'],
            ['city' => 'Iligan',         'terminal_code' => 'ILI',  'name' => 'Iligan',         'code' => 'ILI-STOP', 'type' => 'terminal'],
            ['city' => 'Zamboanga',      'terminal_code' => 'ZAM',  'name' => 'Zamboanga',      'code' => 'ZAM-STOP', 'type' => 'terminal'],
        ];

        foreach ($stops as $data) {
            $city     = City::where('name', $data['city'])->first();
            $terminal = $data['terminal_code']
                ? Terminal::where('code', $data['terminal_code'])->first()
                : null;

            Stop::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name'        => $data['name'],
                    'city_id'     => $city?->id,
                    'terminal_id' => $terminal?->id,
                    'type'        => $data['type'],
                    'status'      => 'active',
                ]
            );
        }

        $this->seedDavaoToGensanStops();
        $this->seedDavaoToCdoStops();
    }

    /**
     * Davao → General Santos: non-stop (0), express (3), regular (13 barangay/city stops).
     */
    private function seedDavaoToGensanStops(): void
    {
        $baseQuery = BusRoute::whereHas('originCity', fn ($q) => $q->where('name', 'Davao City'))
            ->whereHas('destinationCity', fn ($q) => $q->where('name', 'General Santos'));

        $nonStop = (clone $baseQuery)->where('service_type', BusRoute::SERVICE_NON_STOP)->first();
        $express = (clone $baseQuery)->where('service_type', BusRoute::SERVICE_EXPRESS)->first();
        $regular = (clone $baseQuery)->where('service_type', BusRoute::SERVICE_REGULAR)->first();

        if ($nonStop) {
            $nonStop->stops()->sync([]);
        }

        if ($express) {
            $this->attachRouteStops($express, [
                ['code' => 'DVO-TOR',  'order' => 1, 'minutes' => 25,  'fare' => 80.00,  'board' => true,  'alight' => true],
                ['code' => 'DIG-TRM',  'order' => 2, 'minutes' => 55,  'fare' => 180.00, 'board' => true,  'alight' => true],
                ['code' => 'KOR-TRM',  'order' => 3, 'minutes' => 110, 'fare' => 350.00, 'board' => true,  'alight' => true],
            ]);
        }

        if ($regular) {
            $this->attachRouteStops($regular, [
                ['code' => 'DVO-ULAS', 'order' => 1,  'minutes' => 10,  'fare' => 30.00,  'board' => true,  'alight' => true],
                ['code' => 'DVO-MINT','order' => 2,  'minutes' => 18,  'fare' => 45.00,  'board' => true,  'alight' => true],
                ['code' => 'DVO-TOR',  'order' => 3,  'minutes' => 25,  'fare' => 80.00,  'board' => true,  'alight' => true],
                ['code' => 'DIG-STCR','order' => 4,  'minutes' => 50,  'fare' => 150.00, 'board' => true,  'alight' => true],
                ['code' => 'DIG-TRM',  'order' => 5,  'minutes' => 55,  'fare' => 180.00, 'board' => true,  'alight' => true],
                ['code' => 'DIG-SUL',  'order' => 6,  'minutes' => 70,  'fare' => 210.00, 'board' => true,  'alight' => true],
                ['code' => 'DIG-MAL',  'order' => 7,  'minutes' => 85,  'fare' => 240.00, 'board' => true,  'alight' => true],
                ['code' => 'DIG-PAD',  'order' => 8,  'minutes' => 95,  'fare' => 265.00, 'board' => true,  'alight' => true],
                ['code' => 'DIG-HAG',  'order' => 9,  'minutes' => 105, 'fare' => 290.00, 'board' => true,  'alight' => true],
                ['code' => 'DIG-MAT',  'order' => 10, 'minutes' => 115, 'fare' => 315.00, 'board' => true,  'alight' => true],
                ['code' => 'DIG-BAN',  'order' => 11, 'minutes' => 125, 'fare' => 340.00, 'board' => true,  'alight' => true],
                ['code' => 'KOR-TRM',  'order' => 12, 'minutes' => 140, 'fare' => 380.00, 'board' => true,  'alight' => true],
                ['code' => 'GEN-POL',  'order' => 13, 'minutes' => 155, 'fare' => 420.00, 'board' => true,  'alight' => true],
            ]);
        }
    }

    private function seedDavaoToCdoStops(): void
    {
        $route = BusRoute::whereHas('originCity', fn ($q) => $q->where('name', 'Davao City'))
            ->whereHas('destinationCity', fn ($q) => $q->where('name', 'Cagayan de Oro'))
            ->where('service_type', BusRoute::SERVICE_EXPRESS)
            ->first();

        if (! $route) {
            return;
        }

        $this->attachRouteStops($route, [
            ['code' => 'PAN-STOP', 'order' => 1, 'minutes' => 30,  'fare' => 50.00,  'board' => true,  'alight' => false],
            ['code' => 'TGM-STOP', 'order' => 2, 'minutes' => 80,  'fare' => 100.00, 'board' => true,  'alight' => true],
            ['code' => 'BXU-STOP', 'order' => 3, 'minutes' => 240, 'fare' => 300.00, 'board' => true,  'alight' => true],
            ['code' => 'PUE-STOP', 'order' => 4, 'minutes' => 340, 'fare' => 400.00, 'board' => false, 'alight' => true],
        ]);
    }

    private function attachRouteStops(BusRoute $route, array $stopData): void
    {
        $sync = [];

        foreach ($stopData as $data) {
            $stop = Stop::where('code', $data['code'])->first();
            if (! $stop) {
                continue;
            }

            $sync[$stop->id] = [
                'stop_order'          => $data['order'],
                'minutes_from_origin' => $data['minutes'],
                'fare_from_origin'    => $data['fare'],
                'allows_boarding'     => $data['board'],
                'allows_alighting'    => $data['alight'],
            ];
        }

        $route->stops()->sync($sync);
    }
}
