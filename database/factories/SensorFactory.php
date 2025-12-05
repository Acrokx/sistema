<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sensor>
 */
class SensorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'equipo_id' => \App\Models\Equipo::factory(),
            'tipo_sensor' => $this->faker->randomElement(['temperatura', 'vibracion', 'presion']),
            'rango_min' => $this->faker->numberBetween(0, 50),
            'rango_max' => $this->faker->numberBetween(100, 200),
            'limite_alerta_bajo' => $this->faker->numberBetween(10, 30),
            'limite_alerta_alto' => $this->faker->numberBetween(80, 150),
        ];
    }
}
