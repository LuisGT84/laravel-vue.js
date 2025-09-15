<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tarea;
use Illuminate\Http\Request;

class TareaController extends Controller
{
  // GET /api/tareas  → lista con estado, usuario asignado y fechas
  public function index() {
    return Tarea::with('usuario:id,nombre')
      ->select('id','usuario_id','titulo','estado','fecha_vencimiento','created_at')
      ->orderByDesc('id')->paginate(10);
  }

  // POST /api/tareas → crear y asignar a un usuario
  public function store(Request $request) {
    $data = $request->validate([
      'usuario_id' => ['required','exists:usuarios,id'],
      'titulo' => ['required','string','max:150'],
      'descripcion' => ['nullable','string'],
      'estado' => ['nullable','in:pendiente,en_progreso,completada'],
      'fecha_vencimiento' => ['nullable','date'],
    ]);
    $tarea = Tarea::create($data);
    return response()->json($tarea->load('usuario:id,nombre'), 201);
  }

  public function show(Tarea $tarea) { return $tarea->load('usuario:id,nombre'); }

  public function update(Request $request, Tarea $tarea) {
    $data = $request->validate([
      'usuario_id' => ['sometimes','exists:usuarios,id'],
      'titulo' => ['sometimes','string','max:150'],
      'descripcion' => ['nullable','string'],
      'estado' => ['sometimes','in:pendiente,en_progreso,completada'],
      'fecha_vencimiento' => ['nullable','date'],
    ]);
    $tarea->update($data);
    return $tarea->load('usuario:id,nombre');
  }

  public function destroy(Tarea $tarea) {
    $tarea->delete();
    return response()->json(['message' => 'Tarea eliminada']);
  }

  // Extra: exportar CSV de pendientes (abre en Excel)
  public function exportPendientes() {
    $rows = Tarea::with('usuario:id,nombre')
      ->where('estado','pendiente')
      ->get(['id','titulo','estado','fecha_vencimiento','usuario_id']);

    $headers = [
      'Content-Type' => 'text/csv',
      'Content-Disposition' => 'attachment; filename="tareas_pendientes.csv"',
    ];
    $callback = function() use ($rows) {
      $out = fopen('php://output','w');
      fputcsv($out, ['ID','Título','Estado','Fecha Vencimiento','Usuario']);
      foreach ($rows as $t) {
        fputcsv($out, [
          $t->id, $t->titulo, $t->estado,
          optional($t->fecha_vencimiento)->format('Y-m-d'),
          $t->usuario->nombre ?? '',
        ]);
      }
      fclose($out);
    };
    return response()->stream($callback, 200, $headers);
  }
}
