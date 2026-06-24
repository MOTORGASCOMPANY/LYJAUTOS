<?php

namespace App\Http\Livewire;

use App\Models\Certificacion;
use App\Models\Eliminacion;
use App\Models\User;
use Livewire\Component;
use Illuminate\Database\Eloquent\Collection;
use App\Models\CertifiacionExpediente;
use App\Models\Expediente;
use App\Models\Imagen;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VistaEliminacion extends Component
{
    public $inspector,$eliminacion,$eliId, $userId, $cerId;
    public  $user, $certi;

    protected $listeners = ['delete'];


    public function mount(){
        
        $this->eliminacion=Eliminacion::find($this->eliId);
        $this->user = User::find($this->userId);
        $this->certi= Certificacion::find($this->cerId);
    }

    public function render()
    {
        return view('livewire.vista-eliminacion');
    }

    public function cambiaEstadoDeMateriales(Collection $materiales, User $inspector)
    {
        $materiales->each(function ($item, $key) use ($inspector) {
            $item->update(['estado' => 3, "ubicacion" => "En poder de " . $inspector->name]);
        });
    }

    // FUNCION PARA CANCELAR POR DESISTIMIENTO
    public function delete(Certificacion $certificacion)
    {
        // Usamos una transacción para asegurar que si algo falla, no se borre nada a medias
        DB::transaction(function () use ($certificacion) {
            
            // 1. Limpieza de expedientes e imágenes físicas en Storage
            $certExp = CertifiacionExpediente::where('idCertificacion', $certificacion->id)->first();
            if ($certExp) {
                $expe = Expediente::find($certExp->idExpediente);
                if ($expe) {
                    $imgs = Imagen::where('Expediente_idExpediente', '=', $expe->id)->get();
                    foreach ($imgs as $img) {
                        Storage::delete($img->ruta);
                    }
                    $expe->delete(); // Elimina el expediente de la BD
                }
            }

            // 2. Liberar los materiales físicos activos (Vuelven a Estado 3 en posesión del inspector)
            /*
                $materialesActivos = $certificacion->Materiales()->wherePivot('activo', 1)->get();
                if ($materialesActivos->count() > 0) {
                    $this->cambiaEstadoDeMateriales($materialesActivos, $certificacion->Inspector);
                }
            */
            // Corrección para la función delete() del Administrador:
            $materialesAsociados = $certificacion->Materiales; // Trae todos los registros de la intermedia directamente
            if ($materialesAsociados->count() > 0) {
                $this->cambiaEstadoDeMateriales($materialesAsociados, $certificacion->Inspector);
            }            

            // 3. Activar la serie en la tabla 'eliminacion' para que quede disponible para reuso
            // Buscamos el tipo de servicio para saber el grupo exacto antes de actualizar
            $idTipoServicio = $certificacion->Servicio->tipoServicio_idtipoServicio ?? null;
            $grupoServicio = null;
            if (in_array($idTipoServicio, [1, 2, 7, 8, 10, 12, 14])) { $grupoServicio = 'GNV'; }
            elseif (in_array($idTipoServicio, [3, 4, 13])) { $grupoServicio = 'GLP'; }
            elseif ($idTipoServicio == 5) { $grupoServicio = 'MOD'; }

            // Buscamos la fila de eliminación que corresponde a este certificado
            Eliminacion::where('numSerie', $certificacion->numSerie)
                ->where('anioSerie', $certificacion->anioSerie)
                ->where('grupoServicio', $grupoServicio)
                ->where('estado', 1)
                ->update([
                    'estado' => 2 // 2 = Aprobado (Serie oficialmente suelta y disponible en el sistema)
                ]);

            // 4. Desvincular la tabla intermedia y eliminar físicamente el certificado maestro
            $certificacion->Materiales()->detach();
            $certificacion->delete(); 
        });

        //$this->emitTo('administracion-certificaciones', 'render');
        $this->emit("minAlert", ["titulo" => "SISTEMA", "mensaje" => "Certificación eliminada con éxito. El material físico ha sido devuelto al stock del inspector y la serie quedó disponible para el próximo registro.", "icono" => "success"]);
    }

    /*public function delete2(Certificacion $certificacion)
    {
        if ($certificacion->Hoja) {
            $certExp = CertifiacionExpediente::where('idCertificacion', $certificacion->id)->first();
            if ($certExp) {
                $expe = Expediente::find($certExp->idExpediente);
                if ($expe) {

                    $imgs = Imagen::where('Expediente_idExpediente', '=', $expe->id)->get();
                    foreach ($imgs as $img) {
                        Storage::delete($img->ruta);
                    }
                    $expe->delete();
                }
            }

            $this->cambiaEstadoDeMateriales($certificacion->Materiales, $certificacion->Inspector);
            $certificacion->delete();

            //return redirect(route('dashboard'));
        } else {
            if ($certificacion->delete()) {
                $this->emitTo('administracion-certificaciones', 'render');
                $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Se elimino tu servicio pero no se cambio el estado de su formato", "icono" => "warning"]);
                
                //return redirect(route('dashboard'));
            }
        }
    }*/
}
