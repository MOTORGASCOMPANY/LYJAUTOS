<?php

namespace App\Http\Livewire;

use App\Models\Anulacion;
use App\Models\Archivo;
use App\Models\Certificacion;
use App\Models\Eliminacion;
use App\Models\Material;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AnulacionSolicitud as NotificationsCreateSolicitud;
use App\Notifications\SolicitudEliminacion;
use App\Services\CandadoService;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;


class ListaCertificaciones extends Component
{
    use WithPagination;

    public $search, $sort, $direction, $cant, $user;
    public $modal = false, $eliminar, $motivo, $nombre, $imagen, $certiId, $nuevoNumSerie;
    protected $candadoService;
    use WithFileUploads;

    public function __construct()
    {
        parent::__construct(); // Asegúrate de llamar al constructor de la clase padre
        $this->candadoService = app(CandadoService::class); // Inyección de servicio
    }

    protected $queryString = [
        'cant' => ['except' => '10'],
        'sort' => ['except' => 'certificacion.id'],
        'direction' => ['except' => 'desc'],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->user = Auth::id();
        $this->cant = "10";
        $this->sort = 'certificacion.id';
        $this->direction = "desc";
    }

    public function render()
    {

        $certificaciones = Certificacion::numFormato($this->search)
            ->placaVehiculo($this->search)
            ->numSerieVehiculo($this->search) //para buscar preiniciales
            ->idInspector(Auth::id())
            ->orderBy($this->sort, $this->direction)
            ->paginate($this->cant);

        return view('livewire.lista-certificaciones', compact('certificaciones'));
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


    // Para que se usa esta funcion ??
    public function obtieneNumeroHoja($id)
    {
        $certificacion = Certificacion::find($id);
        $hoja = $certificacion->Materiales->where('idTipoMaterial', 1)->first();
        if ($hoja->numSerie != null) {
            return $hoja->numSerie;
        } else {
            return 0;
        }
    }

    public function finalizarPreconversion(Certificacion $certi)
    {
        $ruta = route('finalizarPreconver', ["idCertificacion" => $certi->id]);
        return redirect()->to($ruta);
    }

    /**
     * Código para el Caso 1: Sustituir Material Dañado (Anulación de Formato)
     * Este método se ejecutaría cuando el certificado ya existe, pero el inspector reporta: "Se me trabó el papel en la impresora, necesito cambiar el formato físico".
     */
    // Sustituir Material Dañado (Anulación de Formato)
    public function solicitarAnulacion($certificationId)
    {
        $this->certiId = $certificationId;
        $certificacion = Certificacion::find($certificationId);

        if (!$certificacion) {
            $this->emit("CustomAlert", ["titulo" => "Error", "mensaje" => "Certificación no encontrada.", "icono" => "error"]);
            return;
        }

        // Obtener la hoja física dañada actual usando tu accesor seguro
        $hojaActual = $certificacion->Hoja;

        if (!$hojaActual) {
            $this->emit("CustomAlert", ["titulo" => "Error", "mensaje" => "Esta certificación no cuenta con un formato físico para sustituir.", "icono" => "error"]);
            return;
        }

        // AUTOMATIZACIÓN: Buscar el formato disponible más bajo del mismo tipo en su stock (estado 3)
        $siguienteFormatoDisponible = Material::where('idTipoMaterial', $hojaActual->idTipoMaterial)
            ->where('idUsuario', Auth::id())
            ->where('estado', 3)
            ->orderBy('numSerie', 'asc') // Tomamos el menor correlativo primero
            ->first();

        if ($siguienteFormatoDisponible) {
            // Se lo precargamos en la propiedad vinculada al input para ahorrarle trabajo
            $this->nuevoNumSerie = $siguienteFormatoDisponible->numSerie;
        } else {
            $this->nuevoNumSerie = null; // Queda vacío si se quedó sin stock
        }

        $this->modal = true;
    }    
    public function guardarSolicitudAnulacion()
    {
        $this->validate([
            'motivo' => 'required|string|max:255',
            'imagen' => 'required|image|max:4096',
            'nuevoNumSerie' => 'required|numeric',
        ]);

        try {
            $certificacion = Certificacion::find($this->certiId);

            if (!$certificacion) {
                throw new \Exception('Certificación no encontrada.');
            }

            // Obtener el material dañado actual usando tu accesor seguro
            $hojaDañada = $certificacion->Hoja;
            if (!$hojaDañada) {
                throw new \Exception('No se encontró un formato físico activo para dar de baja.');
            }

            // Validar el nuevo número de serie ingresado (por si el inspector lo modificó manualmente)
            $materialReemplazo = Material::where('numSerie', $this->nuevoNumSerie)
                ->where('idTipoMaterial', $hojaDañada->idTipoMaterial)
                ->where('idUsuario', Auth::id())
                ->where('estado', 3)
                ->first();

            if (!$materialReemplazo) {
                throw new \Exception('El número de serie ingresado no se encuentra disponible en su stock o no corresponde al tipo de formato.');
            }

            // Procesar y guardar la imagen de evidencia
            $placa = $certificacion->Vehiculo->placa ?? 'SinPlaca';
            $nombreArchivo = $placa . '-' . time() . '.' . $this->imagen->getClientOriginalExtension();
            $rutaImagen = $this->imagen->storeAs('anular', $nombreArchivo, 'public');

            if (!$rutaImagen) {
                throw new \Exception('Error al almacenar la imagen de evidencia.');
            }

            // Crear la solicitud de anulación con la nueva estructura de campos en BD
            $solicitudAnulacion = Anulacion::create([
                'motivo' => $this->motivo,
                'idCertificacion' => $certificacion->id,
                'idMaterial' => $hojaDañada->id,           // ID del material dañado
                'idNuevoMaterial' => $materialReemplazo->id, // ID del material de reemplazo
                'estado' => 1,
            ]);

            //Registrar la entrada del archivo en la BD
            Archivo::create([
                'nombre' => $nombreArchivo,
                'ruta' => $rutaImagen,
                'extension' => $this->imagen->getClientOriginalExtension(),
                'idDocReferenciado' => $solicitudAnulacion->id,
            ]);
            
            // Notificar a los usuarios administradores
            $users = User::role(['administrador'])->get();
            Notification::send($users, new NotificationsCreateSolicitud($solicitudAnulacion, $certificacion, Auth::user()));

            // Cierra el modal y limpia estados
            $this->reset(['motivo', 'imagen', 'certiId', 'nuevoNumSerie', 'modal']);
            $this->emit('closeModal');
            
            $this->emit("CustomAlert", ["titulo" => "Solicitud enviada", "mensaje" => "Su solicitud de sustitución de formato ha sido enviada con éxito.", "icono" => "success"]);
        } catch (\Exception $e) {
            $this->emit("CustomAlert", ["titulo" => "Error", "mensaje" => $e->getMessage(), "icono" => "error"]);
            Log::error('Error al guardar la solicitud de anulación: ' . $e->getMessage());
        }
    }
    /*public function solicitarAnulacion($certificationId)
    {
        $this->modal = true;
        $this->certiId = $certificationId;
    }*/
    /*public function guardarSolicitudAnulacion()
    {
        $this->validate([
            'motivo' => 'required',
            'imagen' => 'required|image',
        ]);

        try {
            $certificacion = Certificacion::find($this->certiId);

            if (!$certificacion) {
                throw new \Exception('Certificación no encontrada.');
            }

            if (!$this->candadoService->validarRangoDias($certificacion->created_at)) {
                throw new \Exception('La solicitud no puede enviarse, ya que excede el rango permitido.');
            }

            $placa = $certificacion->Vehiculo->placa ?? 'SinPlaca';
            $nombreArchivo = $placa . '-' . time() . '.' . $this->imagen->getClientOriginalExtension();

            // Especifica el disco 'public' explícitamente
            $rutaImagen = $this->imagen->storeAs('anular', $nombreArchivo, 'public');

            // Verifica si la rutaImagen se generó correctamente
            if (!$rutaImagen) {
                throw new \Exception('Error al almacenar la imagen.');
            }

            // Crear la solicitud de anulación
            $solicitudAnulacion = Anulacion::create([
                'motivo' => $this->motivo,
            ]);

            // Crear la entrada en la tabla de imágenes
            $imagen = Archivo::create([
                'nombre' => $nombreArchivo,
                'ruta' => $rutaImagen,
                'extension' => $this->imagen->getClientOriginalExtension(),
                'idDocReferenciado' => $solicitudAnulacion->id,
            ]);
            
            // Notificar a los usuarios administradores
            $users = User::role(['administrador'])->get();
            Notification::send($users, new NotificationsCreateSolicitud($solicitudAnulacion, $certificacion, Auth::user()));

            // Cierra el modal y resetea las propiedades
            $this->reset(['motivo', 'imagen', 'certiId', 'modal']);
            $this->emit('closeModal');
            // Emitir una notificación de éxito
            $this->emit("CustomAlert", ["titulo" => "Solicitud de anulación", "mensaje" => "Su solicitud de anulación ha sido enviada con éxito.", "icono" => "success"]);
        } catch (\Exception $e) {
            // Manejar cualquier excepción y emitir una notificación de error
            $this->emit("CustomAlert", ["titulo" => "Error", "mensaje" => $e->getMessage(), "icono" => "error"]);

            // Loguear el error para revisarlo más tarde
            Log::error('Error al guardar la solicitud de anulación: ' . $e->getMessage());

            return redirect()->back();
        }
    }*/

    

    /**
     * Código para el Caso 2: Cancelacion por Desistimiento (Liberar Material Físico y reciclar serie del certificado)
     */
    //SOLICITAR CANCELACION POR DESISTIMIENTO
    public function solicitarEliminacion($certificationId)
    {
        //$certificacion = Certificacion::with(['Materiales', 'Servicio'])->find($certificationId);
        $certificacion = Certificacion::with(['Servicio'])->find($certificationId);

        if (!$certificacion) {
            $this->emit("CustomAlert", ["titulo" => "Error", "mensaje" => "La certificación no existe.", "icono" => "error"]);
            return;
        }

        // Identificar el grupo de servicio para el control de la serie
        $idTipoServicio = $certificacion->Servicio->tipoServicio_idtipoServicio ?? null;
        $grupoServicio = null;

        if (in_array($idTipoServicio, [1, 2, 7, 8, 10, 12, 14])) { 
            $grupoServicio = 'GNV'; 
        } elseif (in_array($idTipoServicio, [3, 4, 13])) { 
            $grupoServicio = 'GLP'; 
        } elseif ($idTipoServicio == 5) { 
            $grupoServicio = 'MOD'; 
        }

        // Si el tipo de servicio no maneja correlativos de serie (ej: chip por deterioro), no debería procesarse aquí
        if (is_null($grupoServicio) || is_null($certificacion->numSerie)) {
            $this->emit("CustomAlert", ["titulo" => "Aviso", "mensaje" => "Este tipo de servicio no cuenta con una serie de certificado para reciclar.", "icono" => "warning"]);
            return;
        }

        // Obtener el número de serie del material físico que está activo actualmente
        //$materialActivo = $certificacion->Materiales()->wherePivot('activo', 1)->first();
        //$numSerieMaterial = $materialActivo ? $materialActivo->numSerie : null;

        // SOLUCIÓN AL ERROR: Usamos tu atributo virtual para obtener el formato físico asignado
        $hojaFisica = $certificacion->Hoja; 
        $numSerieMaterial = $hojaFisica ? $hojaFisica->numSerie : null;

        // Crear la solicitud en estado 1 (Pendiente de aprobación por el Administrador)
        $solicitudEliminacion = Eliminacion::create([
            'placa' => $certificacion->placa ?? null,
            'numSerie' => $certificacion->numSerie ?? null,
            'anioSerie' => $certificacion->anioSerie ?? null,
            'grupoServicio' => $grupoServicio,
            'numSerieMaterial' => $numSerieMaterial,
            'tipoServicio' => $certificacion->Servicio->tipoServicio->descripcion ?? null,
            'estado' => 1, // 1 = Pendiente
        ]);

        $users = User::role(['administrador'])->get();
        Notification::send($users, new SolicitudEliminacion($solicitudEliminacion, $certificacion, Auth::user()));
        
        $this->emit("CustomAlert", ["titulo" => "Solicitud de eliminación", "mensaje" => "Su solicitud ha sido enviada al administrador con éxito.", "icono" => "success"]);
    }
    /*public function solicitarEliminacion($certificationId)
    {
        $certificacion = Certificacion::find($certificationId);

        if (!$certificacion) {
            $this->emit("CustomAlert", ["titulo" => "Error", "mensaje" => "La certificación no existe.", "icono" => "error",]);
            return;
        }
        
        if (!$this->candadoService->validarRangoDias($certificacion->created_at)) {
            $this->emit("CustomAlert", [
                "titulo" => "Solicitud no permitida",
                "mensaje" => "No se puede enviar la solicitud porque está fuera del rango de días.",
                "icono" => "error",
            ]);
            return;
        }

        // Si está dentro del rango, crea la solicitud // numSerie tiene que ser la serie del certificado, corregir y capaz agregar la serie de formato para $certificacion->Hoja->numSerie
        $solicitudEliminacion  = Eliminacion::create([
            'placa' => $certificacion->placa ?? null,
            'numSerie' => $certificacion->numSerie ?? null,
            'numSerieMaterial' => $certificacion->Hoja->numSerie ?? null,
            'tipoServicio' => $certificacion->Servicio->tipoServicio->descripcion ?? null,
        ]);

        $users = User::role(['administrador'])->get();
        Notification::send($users, new SolicitudEliminacion($solicitudEliminacion, $certificacion, Auth::user()));
        $this->emit("CustomAlert", ["titulo" => "Solicitud de eliminación enviada", "mensaje" => "Su solicitud de eliminación ha sido enviada con éxito.", "icono" => "success",]);
    }*/
}
