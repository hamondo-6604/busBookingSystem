<?php

namespace Database\Factories;

use App\Models\BusRoute;
use App\Models\City;
use App\Models\Terminal;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusRouteFactory extends Factory
{
    public function definition(): array
    {
        $cities = City::inRandomOrder()->limit(2)->get();

        $origin      = $cities->first()  ?? City::factory()->create();
        $destination = $cities->last()   ?? City::factory()->create();

        if ($origin->id === $destination->id) {
            $destination = City::where('id', '!=', $origin->id)->inRandomOrder()->first()
                           ?? City::factory()->create();
        }

        $serviceType     = fake()->randomElement([
            BusRoute::SERVICE_NON_STOP,
            BusRoute::SERVICE_EXPRESS,
            BusRoute::SERVICE_REGULAR,
        ]);
        $serviceLabel    = match ($serviceType) {
            BusRoute::SERVICE_NON_STOP => 'Non-Stop',
            BusRoute::SERVICE_EXPRESS  => 'Express',
            BusRoute::SERVICE_REGULAR  => 'Regular',
            default                    => ucfirst($serviceType),
        };
        $distanceKm      = fake()->numberBetween(50, 900);
        $durationMinutes = (int) ($distanceKm * fake()->randomFloat(1, 0.8, 1.5));

        $originTerminal = Terminal::where('city_id', $origin->id)->first();
        $destTerminal   = Terminal::where('city_id', $destination->id)->first();

        return [
            'route_name'                 => $origin->name . ' → ' . $destination->name . ' (' . $serviceLabel . ')',
            'origin_city_id'             => $origin->id,
            'destination_city_id'        => $destination->id,
            'origin_terminal_id'         => $originTerminal?->id,
            'destination_terminal_id'    => $destTerminal?->id,
            'service_type'               => $serviceType,
            'distance_km'                => $distanceKm,
            'estimated_duration_minutes' => $durationMinutes,
            'status'                     => 'active',
            'description'                => match ($serviceType) {
                BusRoute::SERVICE_NON_STOP => 'Direct service with 0 intermediate stops.',
                BusRoute::SERVICE_EXPRESS  => 'Few major city stops only.',
                BusRoute::SERVICE_REGULAR  => 'Many barangay and roadside pickup points.',
                default                    => 'Route from ' . $origin->name . ' to ' . $destination->name,
            },
        ];
    }

    // ------------------------------------------------------------------
    // STATES
    // ------------------------------------------------------------------

    public function nonStop(): static
    {
        return $this->state(fn () => [
            'service_type' => BusRoute::SERVICE_NON_STOP,
            'description'  => 'Direct service with 0 intermediate stops.',
        ]);
    }

    public function express(): static
    {
        return $this->state(fn () => [
            'service_type' => BusRoute::SERVICE_EXPRESS,
            'description'  => 'Few major city stops only.',
        ]);
    }

    public function regular(): static
    {
        return $this->state(fn () => [
            'service_type' => BusRoute::SERVICE_REGULAR,
            'description'  => 'Many barangay and roadside pickup points.',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
