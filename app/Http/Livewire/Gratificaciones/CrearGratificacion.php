<?php

namespace App\Http\Livewire\Gratificaciones;

use App\Models\ContratoTrabajo;
use App\Models\Gratificacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CrearGratificacion extends Component
{
    public $open = false;

    public $periodo_mes;
    public $periodo_anio;

    public $contratos = []; // aquí guardamos empleados + cálculos

    protected $listeners = ['abrirCrearGratificacion' => 'abrir'];

    public function abrir()
    {
        $this->reset(['periodo_mes', 'periodo_anio', 'contratos']);
        $this->open = true;
    }

    public function updatedPeriodoMes()
    {
        $this->cargarContratos();
    }

    public function updatedPeriodoAnio()
    {
        $this->cargarContratos();
    }

    public function updatedContratos()
    {
        $this->calcularValores();
    }

    public function cargarContratos()
    {
        if (!$this->periodo_mes || !$this->periodo_anio) {
            $this->contratos = [];
            return;
        }

        $contratos = ContratoTrabajo::where(function ($q) {
            $q->whereNull('cont_externo')
                ->orWhere('cont_externo', 0);
        })
            ->whereHas('empleado', function ($q) {
                $q->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'Inhabilitar');
                });
            })
            ->with('empleado')
            ->get()
            ->sortBy(fn($c) => optional($c->empleado)->name)
            ->values();

        // Convertimos en array para poder modificar campos
        $this->contratos = $contratos->map(function ($c) {
            return [
                'id' => $c->id,
                'empleado' => $c->empleado,
                'fechaInicio' => $c->fechaInicio,
                'pago' => $c->pago,
                'asignacion_familiar' => $c->empleado->asignacion_familiar ?? 0,
                // valores calculados:
                'meses' => 0,
                'asignacion' => 0,
                'monto' => 0,
                'bonificacion' => 0,
                'monto_final' => 0,
                'observacion' => '',
            ];
        })->toArray();

        $this->calcularValores();
    }

    public function quitarEmpleado($index)
    {
        unset($this->contratos[$index]);
        $this->contratos = array_values($this->contratos);
    }

    public function calcularValores()
    {
        if (!$this->periodo_mes || !$this->periodo_anio) return;

        $finPeriodo = Carbon::create($this->periodo_anio, $this->periodo_mes, 30);

        foreach ($this->contratos as $i => $c) {

            // Falta replantear logica de calculo meses
            $inicio = Carbon::parse($c['fechaInicio']);
            $meses = $inicio->diffInMonths($finPeriodo) + 1;

            if ($meses > 6) $meses = 6;
            if ($meses < 0) $meses = 0;

            $this->contratos[$i]['meses'] = $meses;

            // Asignación familiar
            $asignacion = ($c['asignacion_familiar'] == 1) ? 113.00 : 0;
            $this->contratos[$i]['asignacion'] = $asignacion;

            // Monto proporcional
            $montoBase = $c['pago'] / 2;         // regla: gratificación = sueldo / 2
            $montoMensual = $montoBase / 6;      // dividir entre los 6 meses del periodo
            $monto = ($montoMensual * $meses) + $asignacion;
            $this->contratos[$i]['monto'] = round($monto, 2);

            // Bonificación 9%
            $bonif = $monto * 0.09;
            $this->contratos[$i]['bonificacion'] = round($bonif, 2);

            // Monto final
            $this->contratos[$i]['monto_final'] = round($monto + $bonif, 2);
        }
    }

    public function save()
    {
        $this->validate([
            'periodo_mes' => 'required|integer|min:1|max:12',
            'periodo_anio' => 'required|integer|min:2000|max:2100',
            'contratos.*.monto_final' => 'required|numeric|min:0',
            'contratos.*.meses' => 'required|integer|min:0|max:6',
            'contratos.*.observacion' => 'nullable|string|max:255',
        ]);

        if (empty($this->contratos)) {
            $this->emit("minAlert", ["titulo" => "AVISO DEL SISTEMA", "mensaje" => "No hay empleados para guardar.", "icono" => "warning"]);
            return;
        }

        DB::beginTransaction();

        try {

            foreach ($this->contratos as $c) {
                // verificar si ya existe gratificación para ESTE CONTRATO Y PERIODO
                $existe = Gratificacion::where('contrato_id', $c['id'])
                    ->where('periodo_mes', $this->periodo_mes)
                    ->where('periodo_anio', $this->periodo_anio)
                    ->first();

                if ($existe) {
                    // para actualiozar
                    continue;
                }

                // obtener numero de cuenta desde contrato → empleado
                $numeroCuenta = $c['empleado']['numero_cuenta'] ?? null;

                Gratificacion::create([
                    'contrato_id'   => $c['id'],
                    'periodo_mes'   => $this->periodo_mes,
                    'periodo_anio'  => $this->periodo_anio,
                    'fecha_inicio'  => $c['fechaInicio'],
                    'meses_completos' => $c['meses'],
                    'sueldo'        => $c['pago'],
                    'asignacion'    => $c['asignacion'],
                    'monto'         => $c['monto'],
                    'bonificacion'  => $c['bonificacion'],
                    'monto_final'   => $c['monto_final'],
                    'observacion'   => $c['observacion'] ?? '',
                    'numero_cuenta' => $numeroCuenta, 
                ]);
            }

            DB::commit();

            $this->emit("minAlert", ["titulo" => "¡EXCELENTE TRABAJO!", "mensaje" => "Gratificaciones guardadas correctamente.", "icono" => "success"]);
            $this->reset(['open', 'periodo_mes', 'periodo_anio', 'contratos']);
            $this->emit('gratificacionGuardada');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->emit("minAlert", ["titulo" => "ERROR", "mensaje" => $e->getMessage(), "icono" => "error"]);
        }
    }



    public function render()
    {
        return view('livewire.gratificaciones.crear-gratificacion');
    }
}
