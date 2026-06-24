<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AsistenciasExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        // Recibimos la query filtrada desde Livewire
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Colaborador',
            'DNI',
            'Entrada Real',
            'Salida Real',
            'Estado',
            'Tardanza (min)',
            'Horas Trabajadas',
            'Horas Extras (hrs)',
            'Observaciones'
        ];
    }

    public function map($asistencia): array
    {
        return [
            $asistencia->fecha->format('d/m/Y'),
            $asistencia->usuario->name,
            $asistencia->usuario->dni,
            $asistencia->hora_entrada ? $asistencia->hora_entrada->format('H:i:s') : '---',
            $asistencia->hora_salida ? $asistencia->hora_salida->format('H:i:s') : '---',
            $asistencia->estado,
            $asistencia->minutos_tardanza,
            number_format($asistencia->minutos_trabajados / 60, 2),
            number_format($asistencia->horas_extras_minutos / 60, 2),
            $asistencia->observaciones,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']]],
        ];
    }
}
