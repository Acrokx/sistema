<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipo>
 */
class EquipoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->company . ' ' . $this->faker->word,
            'tipo' => $this->faker->randomElement(['Bomba Centrífuga', 'Motor Eléctrico', 'Compresor', 'Turbina']),
            'ubicacion' => $this->faker->randomElement(['Planta Norte', 'Planta Sur', 'Área de Producción', 'Sala de Control']),
            'descripcion' => $this->faker->sentence,
            'fecha_instalacion' => $this->faker->date(),
            'estado' => $this->faker->randomElement(['activo', 'inactivo', 'mantenimiento']),
        ];
    }
}
