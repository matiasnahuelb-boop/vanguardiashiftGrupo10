<?php

namespace Tests\Feature;

use App\Models\Turno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Tests de integración para el módulo de turnos.
 * Usamos SQLite en memoria (configurado en phpunit.xml) para no afectar la DB real.
 */
class TurnoTest extends TestCase
{
    use RefreshDatabase;

    // ── helpers ──────────────────────────────────────────────────────────────

    private function clienteAutenticado(): User
    {
        $user = User::factory()->create(['rol' => 'cliente']);
        Sanctum::actingAs($user);
        return $user;
    }

    private function profesional(): User
    {
        return User::factory()->create(['rol' => 'profesional']);
    }

    private function payload(int $profesionalId, string $fechaHora = null): array
    {
        return [
            'profesional_id' => $profesionalId,
            'fecha_hora'     => $fechaHora ?? now()->addDays(3)->format('Y-m-d H:i:s'),
            'motivo'         => 'Consulta de rutina',
        ];
    }

    // ── tests ─────────────────────────────────────────────────────────────────

    /** @test */
    public function un_cliente_puede_reservar_un_turno_libre(): void
    {
        $cliente     = $this->clienteAutenticado();
        $profesional = $this->profesional();

        $response = $this->postJson('/api/turnos', $this->payload($profesional->id));

        $response->assertStatus(201);
        $this->assertDatabaseHas('turnos', [
            'cliente_id'     => $cliente->id,
            'profesional_id' => $profesional->id,
            'estado'         => 'reservado',
        ]);
        $response->assertJsonPath('data.estado', 'reservado');
    }

    /** @test */
    public function no_se_puede_reservar_un_horario_ya_ocupado(): void
    {
        $this->clienteAutenticado();
        $profesional = $this->profesional();
        $fecha       = now()->addDays(2)->format('Y-m-d H:i:s');

        // Creamos un turno existente en ese horario
        Turno::factory()->create([
            'profesional_id' => $profesional->id,
            'fecha_hora'     => $fecha,
            'estado'         => 'reservado',
        ]);

        $response = $this->postJson('/api/turnos', $this->payload($profesional->id, $fecha));

        $response->assertStatus(409);
        $this->assertDatabaseCount('turnos', 1); // solo el original
    }

    /** @test */
    public function sin_token_no_se_puede_reservar(): void
    {
        // No llamamos a Sanctum::actingAs, el usuario no está autenticado
        $profesional = $this->profesional();

        $response = $this->postJson('/api/turnos', $this->payload($profesional->id));

        $response->assertStatus(401);
        $this->assertDatabaseCount('turnos', 0);
    }

    /** @test */
    public function datos_invalidos_son_rechazados(): void
    {
        $this->clienteAutenticado();

        // Enviamos body vacío a ver qué pasa
        $response = $this->postJson('/api/turnos', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['profesional_id', 'fecha_hora']);
    }

    /** @test */
    public function un_profesional_puede_completar_su_propio_turno(): void
    {
        $profesional = $this->profesional();
        Sanctum::actingAs($profesional);

        $cliente = User::factory()->create(['rol' => 'cliente']);
        $turno   = Turno::factory()->create([
            'profesional_id' => $profesional->id,
            'cliente_id'     => $cliente->id,
            'estado'         => 'reservado',
        ]);

        $response = $this->putJson("/api/turnos/{$turno->id}/estado", ['estado' => 'completado']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'estado' => 'completado']);
    }

    /** @test */
    public function un_profesional_no_puede_modificar_el_turno_de_otro(): void
    {
        $duenio   = $this->profesional();
        $ajeno    = User::factory()->create(['rol' => 'profesional']);
        $cliente  = User::factory()->create(['rol' => 'cliente']);

        $turno = Turno::factory()->create([
            'profesional_id' => $duenio->id,
            'cliente_id'     => $cliente->id,
            'estado'         => 'reservado',
        ]);

        Sanctum::actingAs($ajeno); // el otro profesional intenta modificar

        $response = $this->putJson("/api/turnos/{$turno->id}/estado", ['estado' => 'cancelado']);

        $response->assertStatus(403);
        // el estado no cambió
        $this->assertDatabaseHas('turnos', ['id' => $turno->id, 'estado' => 'reservado']);
    }
}
// VanguardiaShift - 6 Feature Tests
