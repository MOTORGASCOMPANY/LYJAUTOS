<?php

namespace App\Http\Livewire\GastosAdministrativos;

use App\Models\ContratoTrabajo;
use App\Models\GastoAdministrativo;
use App\Models\GastoAdministrativoPersonal;
use App\Models\GastoAdministrativoServicio;
use App\Models\GastoAdministrativoSubservicio;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class GastosAdministrativosForm extends Component
{
    public GastoAdministrativo $gasto;

    public $empleado_id;

    // Variables para gastos por personal
    public $sueldo = 0;
    public $cts = 0;
    public $gratificacion = 0;
    public $essalud = 0;
    public $planilla = 0;
    public $vacacion = 0;
    public $otros = 0;
    // variables y campos edición gastos por personal
    public $mostrarModalPersonal = false;
    public $personalEditId = null;
    public $edit_sueldo = 0;
    public $edit_cts = 0;
    public $edit_gratificacion = 0;
    public $edit_essalud = 0;
    public $edit_planilla = 0;
    public $edit_vacacion = 0;
    public $edit_otros = 0;

    //variables para gastos por servicios
    public $concepto;
    public $monto_presupuestado;
    public $monto;
    public $proveedor;
    // variables y campos edición gastos por servicio
    public $mostrarModalServicio = false;
    public $servicioEditId = null;
    public $edit_concepto;
    public $edit_monto_presupuestado = 0;
    public $edit_monto = 0;
    public $edit_proveedor;
    // variables para agregar subservicios
    public $mostrarModalSubservicio = false;
    public $servicioSeleccionadoId = null;
    public $sub_monto = 0;
    public $sub_fecha;
    public $sub_descripcion;

    protected $listeners = ['render', 'eliminarServicio', 'eliminarSubservicio' ];

    protected $rules = [
        'empleado_id' => 'required',
        'sueldo' => 'numeric|min:0',
        'cts' => 'numeric|min:0',
        'gratificacion' => 'numeric|min:0',
        'essalud' => 'numeric|min:0',
        'planilla' => 'numeric|min:0',
        'vacacion' => 'numeric|min:0',
        'otros' => 'numeric|min:0',

        'concepto' => 'required|string|max:150',
        'monto_presupuestado'    => 'numeric|min:0',
        'monto'    => 'numeric|min:0',
    ];

    // Inicializa el componente cargando el gasto administrativo con su personal y servicios asociados
    public function mount($id)
    {
        $this->gasto = GastoAdministrativo::with(['personal.user', 'servicios.subservicios'])->findOrFail($id);
    }

    protected function calcularCTS()
    {
        if ($this->sueldo <= 0) {
            $this->cts = 0;
            return;
        }

        $base = $this->sueldo * 0.5;
        $sexto = $base / 6;

        // CTS mensual
        $this->cts = round(($base + $sexto) / 12, 2);
    }
    protected function calcularEssalud()
    {
        $this->essalud = round($this->sueldo * 0.09, 2);
    }
    public function updatedEmpleadoId($value)
    {
        $contrato = ContratoTrabajo::with('gratificaciones')
            ->where('idUser', $value)
            ->latest('fechaInicio')
            ->first();

        // Sueldo
        $this->sueldo = $contrato?->sueldo_neto ?? 0;

        // Gratificación mensual (prorrateo / 6)
        $gratificacion = $contrato?->gratificaciones
            ->sortByDesc('created_at')
            ->first();

        $this->gratificacion = $gratificacion
            ? round($gratificacion->monto_final / 6, 2)
            : 0;

        $this->calcularCTS();
        $this->calcularEssalud();
    }
    public function agregarPersonal()
    {
        $this->validate([
            'empleado_id' => 'required',
            'sueldo' => 'numeric|min:0',
            'cts' => 'numeric|min:0',
            'gratificacion' => 'numeric|min:0',
            'planilla' => 'required|numeric|gt:0',
            'essalud' => 'numeric|min:0',
        ]);

        $registro = GastoAdministrativoPersonal::updateOrCreate(
            [
                'gasto_administrativo_id' => $this->gasto->id,
                'user_id' => $this->empleado_id,
            ],
            [
                'sueldo' => $this->sueldo,
                // CTS = ( 50% de sueldo + 1/6 del 50% de sueldo ) x 6 meses (asumimos por ahora que todos tienen los meses computables) / 12
                'cts' => $this->cts,
                'gratificacion' => $this->gratificacion,
                'essalud' => $this->essalud,
                'planilla' => $this->planilla,
                'otros' => $this->otros,
            ]
        );

        $registro->calcularTotal();
        $this->reset(['empleado_id', 'sueldo', 'cts', 'gratificacion', 'essalud', 'planilla', 'otros']);
        $this->gasto->refresh();
    }

    // Registra un nuevo gasto por servicio (padre)
    public function agregarServicio()
    {
        $this->validateOnly('concepto');

        GastoAdministrativoServicio::create([
            'gasto_administrativo_id' => $this->gasto->id,
            'concepto' => $this->concepto,
            'monto_presupuestado' => $this->monto_presupuestado,
            'monto' => $this->monto,
            'proveedor' => $this->proveedor,
        ]);

        $this->reset(['concepto', 'monto_presupuestado', 'monto', 'proveedor']);
        $this->gasto->refresh();
    }

    public function render()
    {
        return view('livewire.gastos-administrativos.gastos-administrativos-form');
    }


    // Abre el modal para agregar un subservicio asociado a un servicio específico
    public function abrirModalSubservicio($servicioId)
    {
        $this->servicioSeleccionadoId = $servicioId;
        $this->reset(['sub_monto', 'sub_fecha', 'sub_descripcion']);
        $this->mostrarModalSubservicio = true;
    }
    // Guarda un subservicio, monto se acumula automáticamente en el servicio padre
    public function guardarSubservicio()
    {
        $this->validateOnly('sub_monto');

        GastoAdministrativoSubservicio::create([
            'gasto_servicio_id' => $this->servicioSeleccionadoId,
            'monto' => $this->sub_monto,
            'fecha' => $this->sub_fecha,
            'descripcion' => $this->sub_descripcion,
            'pagado' => 0,
        ]);

        $this->mostrarModalSubservicio = false;

        // refresca todo
        $this->gasto->refresh();
    }
    // eliminar el subservicio
    public function eliminarSubservicio($id)
    {
        $subservicio = GastoAdministrativoSubservicio::findOrFail($id);
        
        $subservicio->delete();

        // Refrescamos el gasto para actualizar la vista y los totales recalculados en cascada
        $this->gasto->refresh();

        // Alerta de confirmación
        $this->emit("minAlert", ["titulo" => "¡ELIMINADO!", "mensaje" => "El subservicio ha sido eliminado correctamente", "icono" => "success"]);
    }


    //Marca o desmarca un servicio como pagado
    public function togglePago($servicioId)
    {
        $servicio = GastoAdministrativoServicio::find($servicioId);

        if (!$servicio) {
            return;
        }

        $servicio->pagado = !$servicio->pagado;
        $servicio->fecha_pago = $servicio->pagado ? Carbon::now() : null;
        $servicio->save();

        // refrescamos el gasto para que actualice la vista
        $this->gasto->refresh();
    }
    // Carga los datos del servicio y modal edicion
    public function editarServicio($id)
    {
        $servicio = GastoAdministrativoServicio::findOrFail($id);

        $this->servicioEditId = $servicio->id;
        $this->edit_concepto = $servicio->concepto;
        $this->edit_monto_presupuestado = $servicio->monto_presupuestado;
        $this->edit_monto = $servicio->monto;
        $this->edit_proveedor = $servicio->proveedor;

        $this->mostrarModalServicio = true;
    }
    // Actualiza los datos del servicio editado
    public function actualizarServicio()
    {
        $this->validate([
            'edit_concepto' => 'required|string|max:150',
            'edit_monto_presupuestado' => 'numeric|min:0',
            'edit_monto' => 'numeric|min:0',
        ]);

        $servicio = GastoAdministrativoServicio::findOrFail($this->servicioEditId);

        $servicio->update([
            'concepto' => $this->edit_concepto,
            'monto_presupuestado' => $this->edit_monto_presupuestado,
            'monto' => $this->edit_monto,
            'proveedor' => $this->edit_proveedor,
        ]);

        $this->gasto->refresh();
        $this->reset(['mostrarModalServicio', 'servicioEditId', 'edit_concepto', 'edit_monto_presupuestado', 'edit_monto', 'edit_proveedor']);
        $this->emit("minAlert", ["titulo" => "BUEN TRABAJO!", "mensaje" => "Gasto actualizado correctamente", "icono" => "success"]);
    }
    // Elimina un servicio y sus subservicios asociados
    public function eliminarServicio($id)
    {
        $servicio = GastoAdministrativoServicio::findOrFail($id);
        
        // Opcional: Si en la base de datos no tienes el "onDelete('cascade')", 
        // puedes descomentar la siguiente línea para limpiar los subservicios primero:
        $servicio->subservicios()->delete();

        $servicio->delete();

        // Refrescamos el gasto para actualizar los totales y el listado
        $this->gasto->refresh();

        // Alerta de confirmación
        $this->emit("minAlert", ["titulo" => "¡ELIMINADO!", "mensaje" => "El servicio ha sido eliminado correctamente", "icono" => "success"]);
    }


    // Carga los datos del personal y modal edicion
    public function editarPersonal($id)
    {
        $personal = GastoAdministrativoPersonal::findOrFail($id);

        $this->personalEditId = $personal->id;
        $this->edit_sueldo = $personal->sueldo;
        $this->edit_cts = $personal->cts;
        $this->edit_gratificacion = $personal->gratificacion;
        $this->edit_essalud = $personal->essalud;
        $this->edit_planilla = $personal->planilla;
        $this->edit_vacacion = $personal->vacacion;
        $this->edit_otros = $personal->otros;

        $this->mostrarModalPersonal = true;
    }
    // Actualiza los datos del personal editado
    public function actualizarPersonal()
    {
        $this->validate([
            'edit_sueldo' => 'numeric|min:0',
            'edit_cts' => 'numeric|min:0',
            'edit_gratificacion' => 'numeric|min:0',
            'edit_essalud' => 'numeric|min:0',
            'edit_planilla' => 'numeric|min:0',
            'edit_vacacion' => 'numeric|min:0',
            'edit_otros' => 'numeric|min:0',
        ]);

        $personal = GastoAdministrativoPersonal::findOrFail($this->personalEditId);

        $personal->update([
            'sueldo' => $this->edit_sueldo,
            'cts' => $this->edit_cts,
            'gratificacion' => $this->edit_gratificacion,
            'essalud' => $this->edit_essalud,
            'planilla' => $this->edit_planilla,
            'vacacion' => $this->edit_vacacion,
            'otros' => $this->edit_otros,
        ]);

        $personal->calcularTotal();
        $this->gasto->refresh();
        $this->reset(['mostrarModalPersonal', 'personalEditId', 'edit_sueldo', 'edit_cts', 'edit_gratificacion', 'edit_essalud', 'edit_planilla', 'edit_vacacion', 'edit_otros']);
        $this->emit("minAlert", ["titulo" => "BUEN TRABAJO!", "mensaje" => "Gasto actualizado correctamente", "icono" => "success"]);
    }
}
