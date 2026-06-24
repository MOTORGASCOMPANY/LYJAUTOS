<?php

namespace App\Http\Livewire;

use App\Models\ArchivoPago;
use App\Models\Gratificacion;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\Component;

class GratificacionesArchivos extends Component
{
    use WithFileUploads;

    public $open = false;
    public $detalleId;
    public $detalle;
    public $files = [];
    public $tipo = 'boleta';

    public $tipos = [
        'boleta' => 'Boleta',
        'comprobante' => 'Comprobante',
        'otro' => 'Otro'
    ];

    protected $listeners = [
        'abrirGratificaciones' => 'openModal',
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
        $this->detalle = Gratificacion::with('archivosPagos', 'contrato.empleado')
            ->findOrFail($this->detalleId);
    }

    public function upload()
    {
        $this->validate([
            'detalleId' => 'required|exists:gratificaciones,id',
            'files.*' => 'file|max:10240|mimes:pdf,jpeg,png,jpg'
        ]);

        foreach ($this->files as $file) {
            $original = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $path = $file->store('gratificaciones', 'public');

            ArchivoPago::create([
                'archivoable_id' => $this->detalle->id,
                'archivoable_type' => Gratificacion::class,

                'nombre' => $original,
                'ruta' => $path,
                'extension' => $ext,
            ]);
        }

        // Limpia la variable de Livewire
        $this->reset('files');
        $this->open = false;
        $this->loadDetalle();
        $this->emit("minAlert", ["titulo" => "BUEN TRABAJO!", "mensaje" => "Archivos subidos correctamente.", "icono" => "success"]);
    }

    public function deleteArchivo($archivoId)
    {
        $archivo = ArchivoPago::findOrFail($archivoId);

        Storage::disk('public')->delete($archivo->ruta);
        $archivo->delete();

        $this->loadDetalle();
    }

    public function updateTipo($archivoId, $tipo)
    {
        if (!array_key_exists($tipo, $this->tipos)) return;

        ArchivoPago::where('id', $archivoId)->update([
            'tipo' => $tipo
        ]);

        $this->loadDetalle();
    }

    public function render()
    {
        return view('livewire.gratificaciones-archivos');
    }
}
