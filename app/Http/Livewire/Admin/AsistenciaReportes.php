<?php

namespace App\Http\Livewire\Admin;

use App\Exports\AsistenciasExport;
use App\Models\Asistencia;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class AsistenciaReportes extends Component
{
    use WithPagination;

    // Filtros
    public $fechaInicio;
    public $fechaFin;
    public $search = '';
    public $estado = '';
    public $perPage = 15;

    public function mount()
    {
        // Por defecto, mostrar el mes actual
        $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = Carbon::now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getReportDataProperty()
    {
        /** @var \App\Models\User $usuarioActuante */
        $usuarioActuante = auth()->user();

        $query = Asistencia::whereBetween('fecha', [$this->fechaInicio, $this->fechaFin])
            ->with(['usuario']);

        if ($usuarioActuante->hasRole('inspector')) {
            $query->where('user_id', $usuarioActuante->id);
        } else {
            // Si es Admin, puede buscar a cualquiera
            $query->whereHas('usuario', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('dni', 'like', '%' . $this->search . '%');
            });
        }

        return $query->when($this->estado, fn($q) => $q->where('estado', $this->estado))
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_entrada', 'asc');
    }

    public function render()
    {
        return view('livewire.admin.asistencia-reportes', [
            'reportes' => $this->reportData->paginate($this->perPage)
        ]);
    }

    public function exportExcel()
    {
        $query = $this->getReportDataProperty();        
        $nombreArchivo = 'Reporte_Asistencia_' . now()->format('Y-m-d_H-i') . '.xlsx';
        return Excel::download(new AsistenciasExport($query), $nombreArchivo);
    }
    public function exportPDF()
    { /* Lógica de DomPDF */
    }
}
