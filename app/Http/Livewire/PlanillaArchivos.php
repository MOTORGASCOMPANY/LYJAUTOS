<?php

namespace App\Http\Livewire;

use App\Models\ArchivoPago;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\PlanillaDetalle;
use App\Models\PlanillaArchivo;
use Illuminate\Support\Facades\Storage;

class PlanillaArchivos extends Component
{
    use WithFileUploads;

    public $open = false;
    public $detalleId = null;
    public $detalle = null;
    public $files = [];
    public $tipo = 'boleta';
    public $tipos = [
        'boleta' => 'Boleta',
        'comprobante' => 'Comprobante',
        'otro' => 'Otro'
    ];

    protected $listeners = [
        'abrirArchivos' => 'openModal',
        'refreshArchivos' => 'loadDetalle'
    ];

    public function openModal($detalleId)
    {
        $this->reset(['files']);
        $this->detalleId = $detalleId;
        $this->loadDetalle();
        $this->open = true;
    }

    public function loadDetalle()
    {
        //$this->detalle = PlanillaDetalle::with('archivos', 'contrato.empleado', 'usuario')->find($this->detalleId);
        $this->detalle = PlanillaDetalle::with(['archivosPagos', 'contrato.empleado', 'usuario'])->find($this->detalleId);
    }

    public function upload()
    {
        $this->validate([
            'files.*' => 'file|max:10240|mimes:pdf,jpeg,png,jpg'
        ]);

        foreach ($this->files as $file) {
            $original = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $path = $file->store('planillas', 'public');

            /*PlanillaArchivo::create([
                'planilla_detalle_id' => $this->detalleId,
                //'tipo' => $this->tipo ?? null,
                'nombre' => $original,
                'ruta' => $path,
                'extension' => $ext,
            ]);*/
            ArchivoPago::create([
                'archivoable_id'   => $this->detalle->id,
                'archivoable_type' => PlanillaDetalle::class,
                //'tipo'             => 'boleta',
                'nombre'           => $original,
                'ruta'             => $path,
                'extension'        => $ext,
            ]);
        }

        // Limpia la variable de Livewire
        $this->reset('files');
        $this->open = false;
        $this->loadDetalle();
        $this->emit('refreshArchivos');
        $this->emit("minAlert", ["titulo" => "BUEN TRABAJO!", "mensaje" => "Archivos subidos correctamente.", "icono" => "success"]);
    }

    public function deleteArchivo($archivoId)
    {
        $archivo = ArchivoPago::find($archivoId);
        if (!$archivo) return;

        Storage::disk('public')->delete($archivo->ruta);
        $archivo->delete();

        $this->loadDetalle();
        $this->emit('refreshArchivos');
    }

    public function updateTipo($archivoId, $tipo)
    {
        if (!array_key_exists($tipo, $this->tipos)) return;

        $archivo = ArchivoPago::find($archivoId);
        if (!$archivo) return;

        $archivo->tipo = $tipo;
        $archivo->save();

        $this->loadDetalle();
    }

    public function render()
    {
        return view('livewire.planilla-archivos');
    }
}
