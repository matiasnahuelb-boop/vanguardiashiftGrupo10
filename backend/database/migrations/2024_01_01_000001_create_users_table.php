<?php

/**
 * database/migrations/2024_01_01_000001_create_users_table.php
 *
 * Migración para la tabla de usuarios de VanguardiaShift.
 *
 * Las migraciones son archivos versionados que describen la estructura
 * de la base de datos. Laravel las ejecuta en orden cronológico.
 * El método up() crea o modifica, el método down() revierte el cambio.
 *
 * Para ejecutar: php artisan migrate
 * Para revertir: php artisan migrate:rollback
 *
 * @author  VanguardiaShift Team
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla 'users' con todos los campos necesarios.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Clave primaria autoincremental (unsigned big integer)
            $table->id();

            // Datos básicos del usuario
            $table->string('name');
            $table->string('email')->unique(); // unique() crea un índice UNIQUE
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Rol del usuario en el sistema
            // enum() restringe los valores posibles a nivel de base de datos
            // Esto es una capa adicional de validación más allá del código PHP
            $table->string('role')->default('cliente');

            // Campos exclusivos para profesionales (nullable para clientes)
            $table->string('especialidad')->nullable();  // Ej: "Cardiología", "Psicología"
            $table->text('descripcion')->nullable();     // Bio breve del profesional

            // Estado del usuario (para desactivar sin borrar)
            $table->boolean('activo')->default(true);

            // Campos estándar de Laravel
            $table->rememberToken();
            $table->timestamps(); // created_at y updated_at automáticos
            $table->softDeletes(); // deleted_at para borrado lógico
        });

        // Tabla para tokens de autenticación de Sanctum
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable'); // polimórfico: puede pertenecer a cualquier modelo
            $table->string('name');
            $table->string('token', 64)->unique(); // el token hasheado (sha256 = 64 chars)
            $table->text('abilities')->nullable();  // permisos del token (JSON)
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Tabla para restablecimiento de contraseñas
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Revierte los cambios del método up().
     * Se ejecuta con: php artisan migrate:rollback
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
