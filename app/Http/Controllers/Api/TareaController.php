<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class TareaController extends Controller
{
    /** GET /api/tareas/listTareas */
    public function index()
    {
        $tareas = Tarea::with(['usuario:id,nombre,email'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'message' => 'Listado de tareas',
            'data' => $tareas,
        ], 200);
    }

    /** POST /api/tareas/addTarea */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'usuario_id'        => 'required|exists:usuarios,id',
            'titulo'            => 'required|string|max:150',
            'descripcion'       => 'nullable|string',
            'estado'            => 'required|in:pendiente,en_progreso,completada',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        $tarea = Tarea::create($validated);

        return response()->json([
            'message' => 'Tarea creada correctamente',
            'data'    => $tarea,
        ], 201);
    }

    /** GET /api/tareas/exportPendientes  (CSV compatible con Excel) */
    public function exportPendientes()
    {
        $rows = Tarea::with(['usuario:id,nombre,email'])
            ->where('estado', 'pendiente')
            ->orderBy('fecha_vencimiento')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=tareas_pendientes.csv',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID','Título','Descripción','Estado','Vence','Usuario','Email']);
            foreach ($rows as $t) {
                fputcsv($out, [
                    $t->id,
                    $t->titulo,
                    $t->descripcion,
                    $t->estado,
                    $t->fecha_vencimiento,
                    $t->usuario?->nombre,
                    $t->usuario?->email,
                ]);
            }
            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }
}
