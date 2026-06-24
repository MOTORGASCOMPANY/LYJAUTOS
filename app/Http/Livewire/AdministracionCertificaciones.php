<?php

namespace App\Http\Livewire;

use App\Models\CertifiacionExpediente;
use App\Models\Certificacion;
use App\Models\Eliminacion;
use App\Models\Expediente;
use App\Models\Imagen;
use App\Models\Taller;
use App\Models\TipoServicio;
use App\Models\User;
use App\Services\CandadoService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class AdministracionCertificaciones extends Component
{
    use WithPagination;

    public $search, $sort, $direction, $cant, $user, $fechaFin, $dateOptions, $inspectores, $ins, $servicio, $tipos, $talleres, $ta, $fecIni, $fecFin;
    public $editando, $expediente, $identificador, $tipoServicio;
    public $documentos = [];
    public $files = [];
    protected $candadoService; //variable para el candado

    protected $listeners = ['render', 'verificarCandado', 'delete', 'anular', 'deleteChip', 'anularchip'];

    protected $queryString = [
        'cant' => ['except' => '10'],
        'sort' => ['except' => 'certificacion.id'],
        'direction' => ['except' => 'desc'],
        'search' => ['except' => ''],
    ];

    public function __construct()
    {
        parent::__construct(); // Asegúrate de llamar al constructor de la clase padre
        $this->candadoService = app(CandadoService::class); // Inyección de servicio
    }

    protected $casts = [
        'fechaFin' => 'datetime:d-m-Y',
    ];

    public function mount()
    {
        $this->user = Auth::id();
        $this->cant = "10";
        $this->sort = 'certificacion.id';
        $this->direction = "desc";
        $this->fechaFin = date('d/m/Y');
        $this->inspectores = User::role(['inspector', 'supervisor'])->where('id', '!=', Auth::id())->orderBy('name')->get();
        $this->talleres = Taller::all()->sortBy('nombre');
        $this->tipos = TipoServicio::all();
        $this->ins = '';
        $this->fecIni = '';
        $this->fecFin = '';
        $this->servicio = '';
        $this->ta = '';
    }

    public function render()
    {
        $certificaciones = Certificacion::numFormato($this->search)
            ->placaVehiculo($this->search)
            ->idInspector($this->ins)
            ->tipoServicio($this->servicio)
            ->idTaller($this->ta)
            ->rangoFecha($this->fecIni, $this->fecFin)
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant);
        return view('livewire.administracion-certificaciones', compact('certificaciones'));
    }

    public function order($sort)
    {
        if ($this->sort = $sort) {
            if ($this->direction == 'desc') {
                $this->direction = 'asc';
            } else {
                $this->direction = 'desc';
            }
        } else {
            $this->sort = $sort;
            $this->direction = 'asc';
        }
    }

    /*public function anular(Certificacion $certificacion)
    {
        $certificacion->Hoja->update(['estado' => 5]); // estado anulado en MATERIAL
        $certificacion->update(['estado' => 2]); //estado anulado en CERTIFICACION
        $this->emitTo('administracion-certificaciones', 'render');
    }*/


    public function verificarCandado($action, $certificacionId)
    {
        $certificacion = Certificacion::find($certificacionId);

        if (!$this->candadoService->validarRangoDias($certificacion->created_at)) {
            $this->emit('minAlert', [
                'titulo' => 'Acción denegada',
                'mensaje' => 'No puedes realizar esta acción debido a las restricciones del candado.',
                'icono' => 'error'
            ]);
            return;
        }

        // Emitir evento para mostrar el diálogo de confirmación correspondiente
        if ($action === 'deleteCertificacion') {
            $this->emit('deleteCertificacion', $certificacionId);
        } elseif ($action === 'deleteCertificacionChip') {
            $this->emit('deleteCertificacionChip', $certificacionId);
        } elseif ($action === 'anularCertificacion') {
            $this->emit('anularCertificacion', $certificacionId);
        } elseif ($action === 'anularCertificacionChip') {
            $this->emit('anularCertificacionChip', $certificacionId);
        }
    }

    // LOGICA DE ANULAR EN OTROS COMPONENTES -> INSPESCTOR ENVIAR NOTIFICACIONES Y VISTA NOTIFICAICON ADMINISTRADOR ANULA
    public function anular(Certificacion $certificacion)
    {
        if ($certificacion->Hoja) {
            $certificacion->Hoja->update(['estado' => 5]); // estado anulado en MATERIAL
        }
        $certificacion->update(['estado' => 2]); // estado anulado en CERTIFICACION
        $this->emitTo('administracion-certificaciones', 'render');
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
    }

    public function cambiaEstadoDeMateriales(Collection $materiales, User $inspector)
    {
        $materiales->each(function ($item, $key) use ($inspector) {
            $item->update(['estado' => 3, "ubicacion" => "En poder de " . $inspector->name]);
        });
    }

    // FUNCION para Cancelar por Desistimiento (Liberar Material Físico y reciclar serie del certificado)
    public function delete($certificacionId)
    {
        // 1. Buscamos la certificación de forma explícita
        $certificacion = Certificacion::with(['Servicio', 'Materiales'])->find($certificacionId);

        if (!$certificacion) {
            $this->emit("minAlert", ["titulo" => "ERROR", "mensaje" => "La certificación ya no existe o fue eliminada previamente.", "icono" => "error"]);
            return;
        }

        try {
            // Usamos una transacción para asegurar la integridad absoluta de los datos de la empresa
            DB::transaction(function () use ($certificacion) {
                
                // 2. Limpieza de archivos físicos e imágenes guardadas en el servidor
                $certExp = CertifiacionExpediente::where('idCertificacion', $certificacion->id)->first();
                if ($certExp) {
                    $expe = Expediente::find($certExp->idExpediente);
                    if ($expe) {
                        $imgs = Imagen::where('Expediente_idExpediente', $expe->id)->get();
                        foreach ($imgs as $img) {
                            if (Storage::disk('public')->exists($img->ruta)) {
                                Storage::disk('public')->delete($img->ruta);
                            }
                        }
                        $expe->delete(); // Elimina el expediente y por cascada/manual sus registros
                    }
                }

                // 3. Rescatar la información del formato físico ANTES de desvincularlo
                $hojaFisica = $certificacion->Hoja; 
                $numSerieMaterial = $hojaFisica ? $hojaFisica->numSerie : null;

                // 4. Liberar los materiales físicos (Vuelven a Estado 3: En stock del inspector)
                $materialesAsociados = $certificacion->Materiales; 
                if ($materialesAsociados && $materialesAsociados->count() > 0) {
                    // Rescatamos la relación del inspector asignado a la certificación
                    $inspectorAsignado = $certificacion->Inspector ?? User::find($certificacion->idUsuario);
                    $this->cambiaEstadoDeMateriales($materialesAsociados, $inspectorAsignado);
                }            

                // 5. Identificar el grupo de servicio para reciclar el correlativo virtual
                $idTipoServicio = $certificacion->Servicio->tipoServicio_idtipoServicio ?? null;
                $grupoServicio = null;
                
                if (in_array($idTipoServicio, [1, 2, 7, 8, 10, 12, 14])) { 
                    $grupoServicio = 'GNV'; 
                } elseif (in_array($idTipoServicio, [3, 4, 13])) { 
                    $grupoServicio = 'GLP'; 
                } elseif ($idTipoServicio == 5) { 
                    $grupoServicio = 'MOD'; 
                }

                // 6. Crear el registro histórico directamente aprobado (Estado 2) para el reciclaje automático
                Eliminacion::create([
                    'placa' => $certificacion->placa ?? null,
                    'numSerie' => $certificacion->numSerie ?? null,
                    'anioSerie' => $certificacion->anioSerie ?? null,
                    'grupoServicio' => $grupoServicio,
                    'numSerieMaterial' => $numSerieMaterial,
                    'tipoServicio' => $certificacion->Servicio->tipoServicio->descripcion ?? null,
                    'estado' => 2, // 2 = Aprobado directo en el sistema (Liberado y listo para reusar)
                ]);

                // 7. Romper la relación en la tabla intermedia y destruir el certificado maestro
                $certificacion->Materiales()->detach();
                $certificacion->delete(); 
            });

            // Emitir alerta de éxito y refrescar el listado actual del administrador
            $this->emit("minAlert", ["titulo" => "SISTEMA", "mensaje" => "Certificación eliminada con éxito. El material físico ha sido devuelto al stock del inspector y la serie quedó disponible para el próximo registro.", "icono" => "success"]);

        } catch (\Exception $e) {
            Log::error("Error en eliminación directa desde administración: " . $e->getMessage());
            $this->emit("minAlert", ["titulo" => "ERROR", "mensaje" => "No se pudo realizar la eliminación directa: " . $e->getMessage(), "icono" => "error"]);
        }
    }
    /*public function delete(Certificacion $certificacion)
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
        } else {
            if ($certificacion->delete()) {
                $this->emitTo('administracion-certificaciones', 'render');
                $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Se elimino tu servicio pero no se cambio el estado de su formato", "icono" => "warning"]);
            }
        }
    }*/
    public function deleteChip(Certificacion $certificacion)
    {

        if ($certificacion->chipMaterial) {
            //dd($certificacion->chipMaterial);
            $certificacion->chipMaterial->update(['ubicacion' => 'En poder de ' . $certificacion->Inspector->nombre, 'descripcion' => null, 'idUsuario' => $certificacion->Inspector->id, 'estado' => 3]);
            $certificacion->delete();
        } else {
            if ($certificacion->delete()) {
                $this->emitTo('administracion-certificaciones', 'render');
                $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "Se elimino tu servicio pero no se cambio el estado de su formato", "icono" => "warning"]);
            }
        }
    }


    public function generarRuta($cer)
    {
        $certificacion = Certificacion::find($cer);
        $ver = "";
        $descargar = "";
        if ($certificacion) {
            $tipoSer = $certificacion->Servicio->tipoServicio->id;
            switch ($tipoSer) {
                case 1:
                    $ver = route('certificadoInicial', ['id' => $certificacion->id]);
                    break;
                case 2:
                    $ver = route('certificado', ['id' => $certificacion->id]);
                    break;
                default:
                    # code...
                    break;
            }
        }

        return $ver;
    }
    public function generarRutaDescarga($cer)
    {
        $certificacion = Certificacion::find($cer);
        $descargar = "";
        if ($certificacion) {
            $tipoSer = $certificacion->Servicio->tipoServicio->id;
            switch ($tipoSer) {
                case 1:
                    $descargar = route('descargarInicial', ['id' => $certificacion->id]);
                    break;
                case 2:
                    $descargar = route('descargarCertificado', ['id' => $certificacion->id]);
                    break;
                default:
                    # code...
                    break;
            }
        }

        return $descargar;
    }


    public function edit(Certificacion $cert)
    {
        // dd($cert);
        $cert_ex = CertifiacionExpediente::where('idCertificacion', $cert->id)->first();

        // Verificar si $cert_ex es nulo o si no tiene idExpediente
        if (!$cert_ex || is_null($cert_ex->idExpediente)) {
            $this->emit('showErrorMessage', 'Este servicio no tiene expediente.');
            return;
        }

        $expediente = Expediente::findOrFail($cert_ex->idExpediente);
        if ($expediente->estado == 2) {
            $this->pasaDatosExpediente($expediente);
            $this->editando = true;
        } else {
            $this->pasaDatosExpediente($expediente);
            $this->editando = true;
        }
    }

    public function pasaDatosExpediente(Expediente $expediente) //cargar datos del expediente
    {
        $this->expediente = $expediente;
        $this->files = Imagen::where('Expediente_idExpediente', '=', $expediente->id)->whereIn('extension', ['jpg', 'jpeg', 'png', 'gif', 'tif', 'tiff', 'bmp'])->get();
        //dd($this->files);
        $this->documentos = Imagen::where('Expediente_idExpediente', '=', $expediente->id)->whereIn('extension', ['pdf', 'xlsx', 'xls', 'docx', 'doc'])->get();
        $this->identificador = rand();
    }
}
