<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTurnoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profesional_id' => 'required|exists:users,id',
            'fecha_hora'     => 'required|date|after:now',
            'motivo'         => 'nullable|string|max:500',
        ];
    }
}
