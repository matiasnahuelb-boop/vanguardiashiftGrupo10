<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTurnoRequest;
use App\Models\Turno;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TurnoController extends Controller
{
    public function store(StoreTurnoRequest $request): JsonResponse
    {
        $turno = DB::transaction(function () use ($request) {
            $ocupado = Turno::where('profesional_id', $request->profesional_id)
                ->where('fecha_hora', $request->fecha_hora)
                ->whereIn('estado', ['reservado', 'confirmado'])
                ->lockForUpdate()
                ->exists();
            if ($ocupado) {
                abort(409, 'El horario ya no esta disponible.');
            }
            return Turno::create([
                'cliente_id'     => Auth::id(),
                'profesional_id' => $request->profesional_id,
                'fecha_hora'     => $request->fecha_hora,
                'motivo'         => $request->motivo,
                'estado'         => 'reservado',
            ]);
        });
        return response()->json(['mensaje' => 'Turno reservado.', 'data' => $turno], 201);
    }

    public function agenda(Request $request): JsonResponse
    {
        $turnos = Turno::where('profesional_id', Auth::id())
            ->where('fecha_hora', '>=', now())
            ->orderBy('fecha_hora', 'asc')
            ->get();
        return response()->json(['data' => $turnos], 200);
    }

    public function updateEstado(Request $request, int $id): JsonResponse
    {
        $request->validate(['estado' => 'required|in:completado,cancelado']);
        $turno = Turno::where('profesional_id', Auth::id())->find($id);
        if (!$turno) {
            return response()->json(['mensaje' => 'No autorizado.'], 403);
        }
        $turno->update(['estado' => $request->estado]);
        return response()->json(['mensaje' => 'Estado actualizado.', 'data' => $turno], 200);
    }
}
