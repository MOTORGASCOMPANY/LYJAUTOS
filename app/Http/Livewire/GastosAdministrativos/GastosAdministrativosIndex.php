<?php

namespace App\Http\Livewire\GastosAdministrativos;

use App\Models\ContratoTrabajo;
use App\Models\GastoAdministrativo;
use App\Models\GastoAdministrativoPersonal;
use App\Models\GastoAdministrativoServicio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GastosAdministrativosIndex extends Component
{
    public $anio;
    public $mes;

    protected array $serviciosBase = [
        ['concepto' => 'Alquiler local'],
        ['concepto' => 'Internet'],
        ['concepto' => 'Motorizados'],
        ['concepto' => 'Luz'],
        ['concepto' => 'Agua'],
        ['concepto' => 'Celulares'],
        ['concepto' => 'Materiales de Oficina'],
        ['concepto' => 'Sistema'],

        ['concepto' => 'Planilla'],
        ['concepto' => 'Formatos'],
        ['concepto' => 'AFP'],
        ['concepto' => 'ONP'],
        ['concepto' => 'Impuestos a la Renta'],
        ['concepto' => 'IGV'],
    ];

    protected $rules = [
        'anio' => 'required|integer|min:2000',
        'mes'  => 'required|integer|between:1,12',
    ];

    public function mount()
    {
        $this->anio = now()->year;
        $this->mes  = now()->month;
    }

    public function generar()
    {
        $this->validate();

        DB::transaction(function () {

            $gasto = GastoAdministrativo::firstOrCreate(
                [
                    'periodo_anio' => $this->anio,
                    'periodo_mes'  => $this->mes,
                ],
                [
                    'total'  => 0,
                    'estado' => 'abierto',
                ]
            );

            // Evitar duplicar servicios
            if ($gasto->servicios()->count() > 0) {
                redirect()->route('gastos-administrativos.form', $gasto->id);
                return;
            }
            // periodo anterior
            $fechaAnterior = Carbon::create($this->anio, $this->mes, 1)->subMonth();
            $gastoAnterior = GastoAdministrativo::where('periodo_anio', $fechaAnterior->year)
                ->where('periodo_mes', $fechaAnterior->month)
                ->with('servicios')
                ->first();

            // Mapa: concepto => monto real anterior
            $presupuestosAnteriores = [];
            if ($gastoAnterior) {
                $presupuestosAnteriores = $gastoAnterior->servicios
                    ->pluck('monto', 'concepto')
                    ->toArray();
            }


            // Crear servicios base con presupuesto dinámico
            foreach ($this->serviciosBase as $servicio) {
                GastoAdministrativoServicio::create([
                    'gasto_administrativo_id' => $gasto->id,
                    'concepto' => $servicio['concepto'],
                    'monto_presupuestado' => $presupuestosAnteriores[$servicio['concepto']] ?? 0,
                    'monto' => 0,
                    'proveedor' => null,
                    'pagado' => 0,
                ]);
            }

            // Evitar duplicar personal administrativo
            if ($gasto->personal()->count() === 0) {

                // Usuarios con rol administrador (Spatie)
                $administradores = User::role('administrador')->get();

                foreach ($administradores as $user) {
                    // Buscar contrato de trabajo del usuario
                    $contrato = ContratoTrabajo::with('gratificaciones')
                        ->where('idUser', $user->id)
                        ->latest('fechaInicio')
                        ->first();
                    $pagoBase = $contrato?->pago ?? 0;
                    // Monto fijo de asignación familiar (luego se puede mover a config)
                    $asignacionFamiliarMonto = 113.00;
                    // Si el usuario tiene asignación familiar, se suma
                    $sueldo = $pagoBase;
                    if ($user->asignacion_familiar == 1) {
                        $sueldo += $asignacionFamiliarMonto;
                    }

                    // Gratificación mensual (prorrateo / 6)
                    $gratificacionCompleta = $contrato?->gratificaciones
                        ->sortByDesc('created_at')
                        ->first();
                    $gratificacion = $gratificacionCompleta ? round($gratificacionCompleta->monto_final / 6, 2) : 0;
                    
                    // Essalud (9% sueldo + asignación)
                    $essalud = round($sueldo * 0.09, 2);

                    // CTS mensual
                    $cts = round($sueldo * 7 / 144, 2);

                    // Vacación = 50% del sueldo / 12
                    $vacacion = round(($sueldo * 0.5) / 12, 2);

                    $personal = GastoAdministrativoPersonal::create([
                        'gasto_administrativo_id' => $gasto->id,
                        'user_id' => $user->id,
                        'sueldo' => $sueldo,
                        'cts' => $cts,
                        'gratificacion' => $gratificacion,
                        'essalud' => $essalud,
                        'planilla' => 0,
                        'vacacion' => $vacacion,
                        'otros' => 0,
                    ]);
                    $personal->calcularTotal();
                }
            }


            redirect()->route('gastos-administrativos.form', $gasto->id);
        });
    }

    public function render()
    {
        return view('livewire.gastos-administrativos.gastos-administrativos-index');
    }
}
