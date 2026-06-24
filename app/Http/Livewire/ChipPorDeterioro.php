<?php

namespace App\Http\Livewire;

use App\Models\Certificacion;
use App\Models\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Livewire\Component;

class ChipPorDeterioro extends Component
{

    public $chips, $nombre, $placa, $estado = "esperando", $taller, $servicio;
    public $serviexterno = false;

    //variable para fecha del certificado
    public $fechaCertificacion;

    // para ver si los inspector son externos y activar checkbox
    public $inspectorexterno = 0;

    public $tipoRegistro = 'consumo'; // default

    protected $rules = [
        "nombre" => "required|string|min:3",
        "placa" => "required|min:6|max:7"
    ];

    public function mount()
    {
        $this->chips = Material::where([["idUsuario", Auth::id()], ["estado", 3], ["idTipoMaterial", 2]])->get();
        // Obtener el inspector actual
        $insptr = Auth::user();
        // Verificar si el inspector es externo
        $this->inspectorexterno = $insptr->externo == true ? true : null;
        // Si el inspector es externo, activar el checkbox de serviexterno
        $this->serviexterno = $this->inspectorexterno;
    }

    public function render()
    {
        return view('livewire.chip-por-deterioro');
    }

    public function consumirChip()
    {
        $this->validate();

        // Si el inspector no seleccionó fecha, asignamos la fecha actual por defecto
        if (empty($this->fechaCertificacion)) {
            $this->fechaCertificacion = Carbon::today()->toDateString();
        }

        // Validar si la fecha de certificación está dentro del rango permitido (últimos 3 días)
        $fechaVerificar = Carbon::parse($this->fechaCertificacion);
        if ($fechaVerificar->lt(Carbon::today()->subDays(3)) || $fechaVerificar->gt(Carbon::today())) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "La fecha de certificación debe estar dentro de los últimos tres días", "icono" => "warning"]);
            return;
        }

        $chip = $this->chips->first();

        if (empty($chip)) {
            $this->emit("minAlert", ["titulo" => "SIN MATERIAL", "mensaje" => "No cuentas con chips disponibles en tu inventario para realizar este consumo.", "icono" => "error"]);
            return;
        }

        $certificar = Certificacion::certificarChipDeterioro($this->taller,  $this->servicio, $chip, Auth::user(), $this->nombre, $this->placa, $this->serviexterno, $this->fechaCertificacion);

        if ($certificar) {
            $this->estado = "ChipConsumido";
            $this->emit("minAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "El chip fue consumido correctamente", "icono" => "success"]);
        } else {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Ocurrio un error al consumir el chip", "icono" => "warning"]);
        }
    }

    public function tramitarChip()
    {
        $this->validate();

        // Si el inspector no seleccionó fecha, asignamos la fecha actual por defecto
        if (empty($this->fechaCertificacion)) {
            $this->fechaCertificacion = Carbon::today()->toDateString();
        }

        // Validar si la fecha de certificación está dentro del rango permitido (últimos 3 días)
        $fechaVerificar = Carbon::parse($this->fechaCertificacion);
        if ($fechaVerificar->lt(Carbon::today()->subDays(3)) || $fechaVerificar->gt(Carbon::today())) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "La fecha de certificación debe estar dentro de los últimos tres días", "icono" => "warning"]);
            return;
        }

        $cert = Certificacion::tramiteChipDeterioro($this->taller, $this->servicio, Auth::user(), $this->nombre, $this->placa, $this->serviexterno, $this->fechaCertificacion);

        if ($cert) {
            $this->estado = "TramiteProcesado";
            $this->emit("minAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "El trámite fue procesado correctamente", "icono" => "success"]);
        } else {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Ocurrió un error al procesar el trámite", "icono" => "warning"]);
        }
    }
}
