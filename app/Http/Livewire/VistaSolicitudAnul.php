<?php

namespace App\Http\Livewire;

use App\Models\Anulacion;
use App\Models\Archivo;
use App\Models\Certificacion;
use App\Models\Material;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class VistaSolicitudAnul extends Component
{
    public $inspector, $anulacion, $anuId, $userId, $cerId;
    public $images, $user, $certi;

    //protected $listeners = ['render', 'anular', 'anularchip'];

    protected $listeners = ['aprobarSustitucion', 'rechazarSustitucion'];


    public function mount()
    {

        $this->anulacion = Anulacion::find($this->anuId);
        $this->images = Archivo::where("idDocReferenciado", $this->anulacion->id)->first();
        $this->user = User::find($this->userId);
        $this->certi = Certificacion::find($this->cerId);
    }

    public function render()
    {
        return view('livewire.vista-solicitud-anul');
    }

    // APROBAR SUSTITUCIÓN (ENROQUE DE MATERIALES)
    // PROCESO PROFESIONAL DE ANULACIÓN DE FORMATO (AQUI SOLO ANULARA EL MATERIAL HOJA $certificacion->Hoja, QUE PASA CON SERVICIOS QUE USAN CHIP $certificacion->chipMaterial)
    // ASUMIREMOS QUE SOLO CAMBIA LA HOJA PARA LA RE-IMPRESION EL CHIP SIGUE CONSUMIDO.
    public function aprobarSustitucion()
    {
        // Validación de seguridad para evitar doble procesamiento
        if (!$this->anulacion || $this->anulacion->estado != 1) {
            $this->emit("CustomAlert", ["titulo" => "Aviso", "mensaje" => "Esta solicitud ya fue procesada o no es válida.", "icono" => "warning"]);
            return;
        }

        $certificacion = $this->certi;

        // Buscamos ambos materiales usando directamente la data guardada en la solicitud
        $hojaDañada = Material::find($this->anulacion->idMaterial);
        $nuevoMaterial = Material::find($this->anulacion->idNuevoMaterial);

        if (!$certificacion || !$nuevoMaterial || !$hojaDañada) {
            $this->emit("CustomAlert", ["titulo" => "Error", "mensaje" => "Los registros de la certificación o los materiales no están disponibles.", "icono" => "error"]);
            return;
        }

        try {
            DB::transaction(function () use ($certificacion, $hojaDañada, $nuevoMaterial) {
                
                // 1. Quemar/Anular el formato viejo (Estado 5)
                $hojaDañada->update([
                    'estado' => 5, 
                    'ubicacion' => 'Dañado en impresión por: ' . $this->user->name
                ]);

                // 2. Desvincular el formato viejo de la tabla intermedia
                $certificacion->Materiales()->detach($hojaDañada->id);

                // 3. Vincular el nuevo formato precargado a la certificación
                $certificacion->Materiales()->attach($nuevoMaterial->id);

                // 4. Consumir el nuevo formato (Estado 4)
                $nuevoMaterial->update([
                    'estado' => 4,
                    //'ubicacion' => 'Consumido en Certificación ID: ' . $certificacion->id 
                    'ubicacion' => 'En poder del cliente'
                ]);

                // 5. Finalizar la solicitud de anulación como Aprobada
                $this->anulacion->update([
                    'estado' => 2 // 2 = Aprobado
                ]);
            });

            $this->emit("minAlert", ["titulo" => "Sustitución Exitosa", "mensaje" => "El formato antiguo fue anulado (Estado 5) y el nuevo formato se vinculó de inmediato al certificado.", "icono" => "success"]);
            
            $this->emitTo('administracion-certificaciones', 'render');
            //return redirect(route('dashboard'));

        } catch (\Exception $e) {
            Log::error("Error en enroque de formatos (Aprobación): " . $e->getMessage());
            $this->emit("CustomAlert", ["titulo" => "Error", "mensaje" => "No se pudo completar el intercambio: " . $e->getMessage(), "icono" => "error"]);
        }
    }

    /**
     * CASO 1.1: RECHAZAR SUSTITUCIÓN
     * Si el administrador rechaza la solicitud, el nuevo formato debe quedar libre en stock (Estado 3) 
     * para que el inspector lo use en cualquier otro carro, y la solicitud pasa a Estado 3.
     */
    public function rechazarSustitucion()
    {
        if (!$this->anulacion || $this->anulacion->estado != 1) {
            $this->emit("CustomAlert", ["titulo" => "Aviso", "mensaje" => "Esta solicitud ya fue procesada.", "icono" => "warning"]);
            return;
        }

        try {
            DB::transaction(function () {
                // Cambiar el estado de la solicitud a Rechazado
                $this->anulacion->update([
                    'estado' => 3 // 3 = Rechazado
                ]);

                // NOTA: El material nuevo que estaba propuesto (idNuevoMaterial) no sufrió cambios, 
                // sigue estando en Estado 3 (En poder del inspector), por lo que no es necesario alterarlo.
                // El certificado original tampoco se toca porque la solicitud fue denegada.
            });

            $this->emit("CustomAlert", ["titulo" => "Solicitud Rechazada", "mensaje" => "La solicitud ha sido denegada. Los formatos físicos mantienen sus estados originales.", "icono" => "info"]);
            
            return redirect()->route('administracion.solicitudes');

        } catch (\Exception $e) {
            Log::error("Error al rechazar solicitud de sustitución: " . $e->getMessage());
            $this->emit("CustomAlert", ["titulo" => "Error", "mensaje" => "No se pudo procesar el rechazo: " . $e->getMessage(), "icono" => "error"]);
        }
    }

    /*public function anular(Certificacion $certificacion)
    {
        $certificacion->Hoja->update(['estado' => 5]); // estado anulado en MATERIAL
        $certificacion->update(['estado' => 2]); //estado anulado en CERTIFICACION
        $this->emitTo('administracion-certificaciones', 'render');
        return redirect(route('dashboard'));
        
    }
    public function anularchip(Certificacion $certificacion)
    {
        // Anular Hoja si existe
        if ($certificacion->Hoja) {
            $certificacion->Hoja->update(['estado' => 5]); // estado anulado en MATERIAL
        }
        
        // Anular chipMaterial si existe
        if ($certificacion->chipMaterial) {
            $certificacion->chipMaterial->update(['estado' => 3]); // estado posecion inspector
        }
        
        // Anular certificacion
        $certificacion->update(['estado' => 2]); // estado anulado en CERTIFICACION
        
        // Emitir evento para renderizar nuevamente el componente
        $this->emitTo('administracion-certificaciones', 'render');
        return redirect(route('dashboard'));
    }*/
}
