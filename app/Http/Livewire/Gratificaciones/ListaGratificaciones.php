<?php

namespace App\Http\Livewire\Gratificaciones;

use App\Models\Gratificacion;
use Carbon\Carbon;
use Livewire\WithPagination;
use Livewire\Component;

class ListaGratificaciones extends Component
{
    use WithPagination;

    public $periodo_mes = '';
    public $periodo_anio = 2025;
    public $search = '';
    public $cant = 20;

    protected $updatesQueryString = ['periodo_mes', 'periodo_anio', 'search', 'cant'];

    // Resetear paginación al cambiar filtros
    public function updatingPeriodoMes()
    {
        $this->resetPage();
    }
    public function updatingPeriodoAnio()
    {
        $this->resetPage();
    }
    public function getPeriodoSeleccionadoProperty()
    {
        return $this->periodo_mes && $this->periodo_anio;
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingCant()
    {
        $this->resetPage();
    }

    public function togglePago($id)
    {
        $grat = Gratificacion::find($id);
        if ($grat) {
            $grat->pagado = !$grat->pagado;
            $grat->fecha_pago = $grat->pagado ? Carbon::now() : null;
            $grat->save();
        }
    }

    public function render()
    {
        $gratificaciones = Gratificacion::query()
            ->when(
                $this->periodo_mes,
                fn($q) =>
                $q->where('periodo_mes', $this->periodo_mes)
            )
            ->when(
                $this->periodo_anio,
                fn($q) =>
                $q->where('periodo_anio', $this->periodo_anio)
            )
            ->when(
                $this->search,
                fn($q) =>
                $q->where('empleado', 'like', '%' . $this->search . '%')
            )
            ->orderBy('id', 'DESC')
            ->paginate($this->cant);

        $total_monto_final = $gratificaciones->sum('monto_final');

        return view('livewire.gratificaciones.lista-gratificaciones', [
            'gratificaciones' => $gratificaciones,
            'total_monto_final' => $total_monto_final
        ]);
    }
}
