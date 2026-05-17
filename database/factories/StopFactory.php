<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Terminal;
use Illuminate\Database\Eloquent\Factories\Factory;

class StopFactory extends Factory
{
    /** Common barangay / roadside stop names along Mindanao bus corridors. */
    private const BARANGAY_NAMES = [
        'Ulas', 'Mintal', 'Toril', 'Buhangin', 'Panacan', 'Calinan',
        'Sta. Cruz', 'Sulop', 'Malalag', 'Padada', 'Hagonoy', 'Matanao', 'Bansalan',
        'Polomolok', 'Tupi', 'Roxas', 'Maa', 'Bago Aplaya',
    ];

    public function definition(): array
    {
        $city = City::inRandomOrder()->first() ?? City::factory()->create();
        $isBarangay = fake()->boolean(70);

        return [
            'name'        => $isBarangay
                ? fake()->randomElement(self::BARANGAY_NAMES)
                : $city->name,
            'code'        => strtoupper(fake()->unique()->bothify('???-####')),
            'city_id'     => $city->id,
            'terminal_id' => null,
            'address'     => fake()->streetAddress(),
            'latitude'    => fake()->latitude(5.0, 20.0),
            'longitude'   => fake()->longitude(116.0, 127.0),
            'type'        => $isBarangay ? 'barangay' : fake()->randomElement(['pickup', 'dropoff', 'waypoint']),
            'status'      => 'active',
        ];
    }

    // ------------------------------------------------------------------
    // STATES
    // ------------------------------------------------------------------

    public function terminal(): static
    {
        return $this->state(function () {
            $terminal = Terminal::inRandomOrder()->first() ?? Terminal::factory()->create();

            return [
                'name'        => $terminal->city?->name ?? 'Terminal',
                'type'        => 'terminal',
                'terminal_id' => $terminal->id,
                'city_id'     => $terminal->city_id,
            ];
        });
    }

    public function barangay(): static
    {
        return $this->state(fn () => [
            'name' => fake()->randomElement(self::BARANGAY_NAMES),
            'type' => 'barangay',
        ]);
    }

    public function pickup(): static
    {
        return $this->state(fn () => ['type' => 'pickup']);
    }

    public function dropoff(): static
    {
        return $this->state(fn () => ['type' => 'dropoff']);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}
