<?php

/**
 * database/migrations/2024_01_01_000002_create_turnos_table.php
 *
 * Migración para la tabla 'turnos' de VanguardiaShift.
 *
 * Esta tabla es el corazón del sistema: cada fila representa
 * un turno médico con su paciente, profesional, horario y estado.
 *
 * Índices creados:
 *  - (profesional_id, fecha_hora): la consulta más frecuente del sistema
 *    es "¿hay algún turno para este profesional en este horario?"
 *    El índice compuesto hace esa consulta muy eficiente.
 *  - (cliente_id): para listar los turnos de un paciente rápidamente
 *
 * @author  VanguardiaShift Team
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla 'turnos'.
     */
    public function up(): void
    {
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();

            // Claves foráneas hacia la tabla users
            // constrained() crea automáticamente la FK con ON DELETE RESTRICT
            $table->foreignId('cliente_id')
                ->constrained('users')
                ->restrictOnDelete(); // No permite borrar un usuario con turnos activos

            $table->foreignId('profesional_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Fecha y hora del turno (datetime incluye ambos)
            $table->dateTime('fecha_hora');

            // Estado del turno — flujo: reservado → confirmado → completado
            //                                    ↓
            //                                cancelado
            $table->enum('estado', ['reservado', 'confirmado', 'completado', 'cancelado'])
                ->default('reservado');

            // Información del turno
            $table->string('motivo', 500)->nullable();         // Razón de la consulta
            $table->text('notas_cliente')->nullable();         // Notas del paciente
            $table->text('notas_profesional')->nullable();     // Notas post-consulta

            // Timestamps y borrado lógico
            $table->timestamps();
            $table->softDeletes(); // El historial nunca se pierde

            // ── ÍNDICES ──────────────────────────────────────────────────────
            // Índice compuesto para la consulta de concurrencia:
            // "¿Existe un turno reservado para este profesional en este horario?"
            // Esta consulta ocurre en CADA reserva. Sin índice sería un full scan.
            $table->index(['profesional_id', 'fecha_hora'], 'idx_profesional_fecha');

            // Índice para listar los turnos de un cliente
            $table->index('cliente_id', 'idx_cliente');
            // Índice UNIQUE para prevenir dobles reservas
            $table->unique(['profesional_id', 'fecha_hora'], 'uk_profesional_horario');
        });
    }

    /**
     * Elimina la tabla 'turnos'.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
