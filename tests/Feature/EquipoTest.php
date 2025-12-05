<?php

namespace Tests\Feature;

use App\Models\Equipo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipoTest extends TestCase
{
    use RefreshDatabase;

    public function test_equipo_puede_ser_creado()
    {
        $equipo = Equipo::create([
            'nombre' => 'Bomba Principal',
            'tipo' => 'Bomba Centrífuga',
            'ubicacion' => 'Planta Norte',
            'descripcion' => 'Bomba de agua principal',
            'fecha_instalacion' => '2023-01-15',
            'estado' => 'activo'
        ]);

        $this->assertInstanceOf(Equipo::class, $equipo);
        $this->assertEquals('Bomba Principal', $equipo->nombre);
        $this->assertEquals('activo', $equipo->estado);
    }

    public function test_equipo_puede_tener_sensores()
    {
        $equipo = Equipo::factory()->create();
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $equipo->sensores);
    }

    public function test_puede_obtener_todos_los_equipos_via_api()
    {
        Equipo::factory()->count(3)->create();

        // Hacer petición GET
        $response = $this->getJson('/api/equipos');

        // Verificar respuesta
        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_puede_crear_equipo_via_api()
    {
        $datosEquipo = [
            'nombre' => 'Compresor Test',
            'tipo' => 'Compresor',
            'ubicacion' => 'Área de Pruebas',
            'descripcion' => 'Equipo para testing',
            'fecha_instalacion' => '2024-01-01',
            'estado' => 'activo'
        ];

        $response = $this->postJson('/api/equipos', $datosEquipo);

        $response->assertStatus(201)
            ->assertJson(['nombre' => 'Compresor Test']);

        $this->assertDatabaseHas('equipos', $datosEquipo);
    }

    public function test_puede_obtener_equipo_especifico()
    {
        $equipo = Equipo::factory()->create();

        $response = $this->getJson("/api/equipos/{$equipo->id}");

        $response->assertStatus(200)
            ->assertJson(['id' => $equipo->id]);
    }
}