<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTurnoRequest;
use App\Models\Turno;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TurnoController extends Controller
{
    // Estados que puede tomar un turno una vez reservado
    private const ESTADOS_PERMITIDOS = ['completado', 'cancelado'];

    /**
     * Crea una nueva reserva de turno.
     * Usa transacción + lockForUpdate para evitar que dos personas
     * reserven el mismo horario al mismo tiempo.
     */
    public function store(StoreTurnoRequest $request): JsonResponse
    {
        $turno = DB::transaction(function () use ($request) {

            // Verificamos si el horario ya está ocupado.
            // lockForUpdate bloquea la fila mientras procesamos,
            // así un segundo usuario no puede leer "libre" al mismo tiempo.
            $ocupado = Turno::where('profesional_id', $request->profesional_id)
                ->where('fecha_hora', $request->fecha_hora)
                ->whereIn('estado', ['reservado', 'confirmado'])
                ->lockForUpdate()
                ->exists();

            if ($ocupado) {
                abort(409, 'El horario ya no está disponible.');
            }

            return Turno::create([
                'cliente_id'     => Auth::id(),
                'profesional_id' => $request->profesional_id,
                'fecha_hora'     => $request->fecha_hora,
                'motivo'         => $request->motivo,
                'estado'         => 'reservado',
                'notas_cliente'  => $request->notas_cliente,
            ]);
        });

        Log::info('Turno reservado', [
            'turno_id'   => $turno->id,
            'cliente'    => $turno->cliente_id,
            'profesional'=> $turno->profesional_id,
        ]);

        return response()->json([
            'mensaje' => 'Turno reservado correctamente.',
            'data'    => $turno->load(['cliente', 'profesional']),
        ], 201);
    }

    /**
     * Permite al profesional cambiar el estado de un turno propio.
     * No puede modificar turnos de otros profesionales.
     */
    public function cambiarEstado(Request $request, $id): JsonResponse
    {
        $request->validate([
            'estado' => ['required', 'string', 'in:' . implode(',', self::ESTADOS_PERMITIDOS)],
        ]);

        $turno = Turno::findOrFail($id);

        // Solo el profesional dueño del turno puede modificarlo
        if ($turno->profesional_id !== Auth::id()) {
            return response()->json(['mensaje' => 'No podés modificar este turno.'], 403);
        }

        // Un turno terminado no puede cambiarse de nuevo
        if (in_array($turno->estado, ['completado', 'cancelado'])) {
            return response()->json([
                'mensaje' => "El turno ya está en estado '{$turno->estado}' y no puede modificarse.",
            ], 422);
        }

        $estadoAnterior = $turno->estado;
        $turno->update(['estado' => $request->estado]);

        Log::info('Estado de turno cambiado', [
            'turno_id' => $turno->id,
            'de'       => $estadoAnterior,
            'a'        => $request->estado,
        ]);

        return response()->json([
            'mensaje' => 'Estado actualizado.',
            'data'    => $turno->fresh(['cliente', 'profesional']),
        ], 200);
    }

    /**
     * Devuelve la agenda del profesional autenticado.
     * Se puede filtrar por fecha con ?fecha=YYYY-MM-DD.
     */
    public function agendaProfesional(Request $request): JsonResponse
    {
        $request->validate([
            'fecha' => 'nullable|date_format:Y-m-d',
        ]);

        $query = Turno::where('profesional_id', Auth::id())
            ->with('cliente')
            ->orderBy('fecha_hora', 'asc');

        if ($request->filled('fecha')) {
            $query->whereDate('fecha_hora', $request->fecha);
        } else {
            $query->where('fecha_hora', '>=', now());
        }

        $turnos = $query->get();

        return response()->json([
            'mensaje'      => 'Agenda cargada.',
            'total_turnos' => $turnos->count(),
            'data'         => $turnos,
        ], 200);
    }
}