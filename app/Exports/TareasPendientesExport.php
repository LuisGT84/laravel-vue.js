<?php

namespace App\Exports;

use App\Models\Tarea;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TareasPendientesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return Tarea::with(['usuario:id,nombre,email'])
            ->where('estado', 'pendiente')
            ->orderBy('fecha_vencimiento')
            ->get();
    }

    public function headings(): array
    {
        return ['ID', 'Título', 'Descripción', 'Estado', 'Vence', 'Usuario', 'Email'];
    }

    public function map($t): array
    {
        return [
            $t->id,
            $t->titulo,
            $t->descripcion,
            $t->estado,
            $t->fecha_vencimiento,
            optional($t->usuario)->nombre,
            optional($t->usuario)->email,
        ];
    }
}
