<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class IAService
{
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = 'http://127.0.0.1:8001';
    }

    /**
     * Consulta la API de IA para predecir fallos
     */
    public function predecirFallo(float $temperatura, float $vibracion, float $presion, float $horasOperacion): array
    {
        try {
            $response = Http::post($this->apiUrl . '/predecir', [
                'temperatura' => $temperatura,
                'vibracion' => $vibracion,
                'presion' => $presion,
                'horas_operacion' => $horasOperacion
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => 'No se pudo conectar con el servicio de IA'];
        } catch (\Exception $e) {
            return ['error' => 'Error de comunicación: ' . $e->getMessage()];
        }
    }

    /**
     * Verifica si el servicio de IA está disponible
     */
    public function verificarDisponibilidad(): bool
    {
        try {
            $response = Http::get($this->apiUrl . '/docs');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}