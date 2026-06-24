<?php

namespace App\Http\Livewire\Contabilidad;

use App\Models\FacturaContabilidad;
use Livewire\Component;
use Livewire\WithPagination;

class ListaFacturasContabilidad extends Component
{
    use WithPagination;

    public $cant = 10;
    public $search = '';
    public $filtro_tipo = 'todos';
    public $fecIni = '', $fecFin = '';

    public $facturaId;
    public $tipo, $numero, $proveedor, $ruc, $igv, $fecha_emision, $monto_total;

    public $openEditarModal = false;

    protected $listeners = ['facturaGuardada' => '$refresh',  'confirmarEliminacion' => 'eliminar'];

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingFiltroTipo()
    {
        $this->resetPage();
    }
    public function updatingFecIni()
    {
        $this->resetPage();
    }
    public function updatingFecFin()
    {
        $this->resetPage();
    }

    /*public function render()
    {
        $facturas = FacturaContabilidad::when($this->filtro_tipo != 'todos', function ($q) {
            $q->where('tipo', $this->filtro_tipo);
        })
            ->where(function ($q) {
                $q->where('numero', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('proveedor', 'LIKE', '%' . $this->search . '%');
            })
            ->orderBy('id', 'DESC')
            ->paginate($this->cant);


        return view('livewire.contabilidad.lista-facturas-contabilidad', compact('facturas'));
    }*/
    public function render()
    {
        $query = FacturaContabilidad::query()
            ->when($this->filtro_tipo != 'todos', function ($q) {
                $q->where('tipo', $this->filtro_tipo);
            })
            ->when($this->fecIni, function ($q) {
                $q->whereDate('fecha_emision', '>=', $this->fecIni);
            })
            ->when($this->fecFin, function ($q) {
                $q->whereDate('fecha_emision', '<=', $this->fecFin);
            })
            ->where(function ($q) {
                $q->where('numero', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('proveedor', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('ruc', 'LIKE', '%' . $this->search . '%');
            });

        // TOTALES GLOBALES: Clonamos la query para obtener sumatorias limpias del estado actual de filtros
        $totalIgv = (clone $query)->sum('igv');
        $totalMonto = (clone $query)->sum('monto_total');

        // Paginación
        $facturas = $query->orderBy('id', 'DESC')
            ->paginate($this->cant);

        return view('livewire.contabilidad.lista-facturas-contabilidad', compact('facturas', 'totalIgv', 'totalMonto'));
    }



    public function editar($id)
    {
        $factura = FacturaContabilidad::find($id);

        if (!$factura) {
            $this->dispatchBrowserEvent('alert', ['message' => 'Factura no encontrada']);
            return;
        }

        $this->facturaId = $factura->id;
        $this->tipo = $factura->tipo;
        $this->numero = $factura->numero;
        $this->proveedor = $factura->proveedor;
        $this->ruc = $factura->ruc;
        $this->igv = $factura->igv;
        $this->fecha_emision = $factura->fecha_emision;
        $this->monto_total = $factura->monto_total;

        $this->openEditarModal = true;
    }
    public function guardarEdicion()
    {
        $this->validate([
            'numero' => 'nullable|string|max:50',
            'proveedor' => 'nullable|string|max:255',
            'fecha_emision' => 'nullable|date',
            'monto_total' => 'required|numeric|min:0',
        ]);

        $factura = FacturaContabilidad::findOrFail($this->facturaId);

        $factura->update([
            'numero' => $this->numero,
            'proveedor' => $this->proveedor,
            'ruc' => $this->ruc,
            'igv' => $this->igv,
            'fecha_emision' => $this->fecha_emision,
            'monto_total' => $this->monto_total,
        ]);

        $this->openEditarModal = false;
        $this->emit('facturaGuardada');
        $this->emit("minAlert", ["titulo" => "¡COMPLETADO!", "mensaje" => "Factura actualizada correctamente", "icono" => "success"]);
    }


    public function eliminar($id)
    {
        $factura = FacturaContabilidad::find($id);

        if (!$factura) {
            $this->dispatchBrowserEvent('alert', [
                'message' => 'Factura no encontrada'
            ]);
            return;
        }

        $factura->delete();

        $this->dispatchBrowserEvent('alert', [
            'message' => 'Factura eliminada correctamente'
        ]);
    }
}
