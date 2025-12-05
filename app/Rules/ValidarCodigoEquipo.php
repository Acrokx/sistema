<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidarCodigoEquipo implements Rule
{
    public function passes($attribute, $value)
    {
        // Formato: EQ-YYYY-NNNN (ej: EQ-2024-0001)
        $pattern = '/^EQ-\d{4}-\d{4}$/';

        if (!preg_match($pattern, $value)) {
            return false;
        }

        // Extraer año del código (pos 3 al 6)
        $year = (int) substr($value, 3, 4);
        $currentYear = (int) date('Y');

        // El año debe estar dentro de los últimos 5 años e incluir el año actual
        return $year >= ($currentYear - 5) && $year <= $currentYear;
    }

    public function message()
    {
        return 'El código del equipo debe tener el formato EQ-YYYY-NNNN y el año debe estar dentro del rango permitido.';
    }
}
