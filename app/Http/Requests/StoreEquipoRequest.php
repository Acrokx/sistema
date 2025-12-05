<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipoRequest extends FormRequest
{
    public function authorize()
    {
        // Solo usuarios autenticados con rol técnico, admin o supervisor
        return auth()->check() &&
            auth()->user()->hasAnyRole(['tecnico', 'admin', 'supervisor']);
    }

    public function rules()
    {
        return [
            'nombre' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'unique:equipos,nombre',
                'regex:/^[a-zA-Z0-9\s\-_]+$/' // Solo letras, números, espacios y guiones
            ],
            'tipo' => [
                'required',
                'string',
                'in:bomba,compresor,motor,generador,turbina'
            ],
            'ubicacion' => [
                'required',
                'string',
                'max:100'
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:500'
            ],
            'fecha_instalacion' => [
                'required',
                'date',
                'before_or_equal:today',
                'after:1990-01-01'
            ],
            'costo' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99'
            ],
            'proveedor_id' => [
                'required',
                'exists:proveedores,id'
            ],
            'especificaciones' => [
                'nullable',
                'array'
            ],
            'especificaciones.potencia' => [
                'nullable',
                'numeric',
                'min:0'
            ],
            'especificaciones.voltaje' => [
                'nullable',
                'numeric',
                'min:0'
            ]
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre del equipo es obligatorio',
            'nombre.unique' => 'Ya existe un equipo con este nombre',
            'nombre.regex' => 'El nombre solo puede contener letras, números, espacios y guiones',
            'tipo.in' => 'El tipo de equipo debe ser: bomba, compresor, motor, generador o turbina',
            'fecha_instalacion.before_or_equal' => 'La fecha de instalación no puede ser futura',
            'fecha_instalacion.after' => 'La fecha de instalación debe ser posterior a 1990',
            'costo.min' => 'El costo no puede ser negativo',
            'proveedor_id.exists' => 'El proveedor seleccionado no existe'
        ];
    }

    public function attributes()
    {
        return [
            'nombre' => 'nombre del equipo',
            'tipo' => 'tipo de equipo',
            'ubicacion' => 'ubicación',
            'fecha_instalacion' => 'fecha de instalación',
            'proveedor_id' => 'proveedor'
        ];
    }
}
