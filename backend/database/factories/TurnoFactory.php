<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TurnoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id'     => User::factory(),
            'profesional_id' => User::factory()->create(['role' => 'profesional'])->id,
            'fecha_hora'     => now()->addDays(rand(1, 30)),
            'motivo'         => fake()->sentence(),
            'estado'         => 'reservado',
        ];
    }
}
