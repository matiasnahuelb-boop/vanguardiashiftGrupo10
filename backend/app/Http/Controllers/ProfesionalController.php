<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Turno;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfesionalController extends Controller
{
    /**
     * Lista todos los profesionales activos.
     * GET /api/profesionales
     */
    public function index(): JsonResponse
    {
        $profesionales = User::where('rol', 'profesional')
            ->where('activo', true)
            ->select('id', 'name', 'email', 'especialidad', 'descripcion')
            ->get();

        return response()->json([
            'mensaje' => 'Profesionales activos.',
            'data' => $profesionales,
        ], 200);
    }

    /**
     * Devuelve los horarios disponibles de un profesional para una fecha.
     * GET /api/profesionales/{id}/disponibilidad?fecha=YYYY-MM-DD
     */
    public function disponibilidad(Request $request, $id): JsonResponse
    {
        $request->validate([
            'fecha' => 'required|date_format:Y-m-d',
        ]);

        $profesional = User::where('id', $id)
            ->where('rol', 'profesional')
            ->where('activo', true)
            ->firstOrFail();

        $horariosAtencion = [];
        for ($h = 9; $h < 18; $h++) {
            $horariosAtencion[] = sprintf('%02d:00', $h);
            $horariosAtencion[] = sprintf('%02d:30', $h);
        }

        $fecha = $request->fecha;
        $turnosOcupados = Turno::where('profesional_id', $id)
            ->whereDate('fecha_hora', $fecha)
            ->whereIn('estado', ['reservado', 'confirmado'])
            ->get()
            ->map(fn($t) => $t->fecha_hora->format('H:i'))
            ->toArray();

        $disponibles = array_diff($horariosAtencion, $turnosOcupados);

        return response()->json([
            'mensaje' => 'Disponibilidad del ' . $fecha,
            'profesional' => [
                'id' => $profesional->id,
                'name' => $profesional->name,
                'especialidad' => $profesional->especialidad,
            ],
            'fecha' => $fecha,
            'horarios_disponibles' => array_values($disponibles),
            'horarios_ocupados' => $turnosOcupados,
        ], 200);
    }
}// VanguardiaShift - Listado de profesionales
