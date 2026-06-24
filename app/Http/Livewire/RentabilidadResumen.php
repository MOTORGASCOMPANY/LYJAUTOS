<?php

namespace App\Http\Livewire;

use App\Models\ContratoTrabajo;
use App\Models\GastoAdministrativo;
use App\Models\Taller;
use App\Models\User;
use App\Services\DataService;
use Carbon\Carbon;
use Livewire\Component;

class RentabilidadResumen extends Component
{
    // VARIABLE PARA FILTRO
    public $fechaInicio, $fechaFin;
    // FILTROS QUE NO USO PERO PORSEACASO PARA DATASERVICE
    public $ins = [], $taller = [], $servicio;

    //VARIABLES PARA REPORTE EXTERNOS
    public array $reporteExternos = [];
    public float $totalExternos = 0.0;

    //VARIABLES PARA REPORTE TALLERES
    public $asistir;
    public $semanales, $diarios, $otros;
    public float $totalTalleres = 0.0;

    // VARIABLE TOTAL VENTAS   
    public float $totalVentas = 0.0;

    public float $totalGastosAdministrativos = 0.0;

    // En las propiedades
    public array $resumenFinal = [];

    protected $dataService;

    protected $rules = [
        "fechaInicio" => 'required|date',
        "fechaFin" => 'required|date',
    ];

    public function __construct()
    {
        parent::__construct(); // Asegúrate de llamar al constructor de la clase padre
        $this->dataService = app(DataService::class); // Inyección de servicio
    }
    public function mount()
    {
        $this->semanales = collect();
        $this->diarios   = collect();
        $this->otros     = collect();
    }

    /** Flujo general
     * reportes()
     *→ procesarExternos()
     *→ procesarTalleres()
     *→ calcularTotalVentas()
     *  → calcularGastosAdministrativos()
     *     → costos totales externos
     *     → costos totales talleres
     */

    public function render()
    {
        return view('livewire.rentabilidad-resumen');
    }

    //JUNTAR REPORTES
    public function reportes()
    {
        $this->validate();

        $datos = $this->dataService->procesar($this->ins, $this->taller, $this->servicio, $this->fechaInicio, $this->fechaFin);
        $this->procesarExternos($datos);
        $this->procesarTalleres($datos);

        $this->calcularTotalVentas();
    }

    // Calcular total de ventas
    private function calcularTotalVentas(): void
    {
        $this->totalVentas = round($this->totalExternos + $this->totalTalleres, 2);

        // ahora sí calculamos porcentajes de externos
        if ($this->totalVentas > 0) {
            foreach ($this->reporteExternos as $key => $data) {
                $this->reporteExternos[$key]['porcentaje'] = round(($data['total'] / $this->totalVentas) * 100, 4);
            }

            foreach (['semanales', 'diarios', 'otros'] as $grupo) {
                $this->$grupo = $this->$grupo->map(function ($item) {
                    $item['porcentaje'] = round(($item['total'] / $this->totalVentas) * 100, 4);
                    return $item;
                });
            }

            $this->calcularGastosAdministrativos();
        }
    }

    private function calcularGastosAdministrativos(): void
    {
        $this->totalGastosAdministrativos = $this->obtenerTotalGastosAdministrativos();

        if ($this->totalGastosAdministrativos <= 0) {
            $this->calcularResumenFinal();
            return;
        }

        foreach ($this->reporteExternos as $key => $data) {
            $gastosAdm = round(($data['porcentaje'] / 100) * $this->totalGastosAdministrativos, 2);
            $costosTotales = $this->calcularCostosTotalesGrupo($data['items'], true, $gastosAdm);

            $this->reporteExternos[$key]['gastos_adm'] = $gastosAdm;
            //$this->reporteExternos[$key]['costos_totales'] = $this->calcularCostosTotalesGrupo($data['items'], true, $gastosAdm);
            $this->reporteExternos[$key]['costos_totales'] = $costosTotales;
            $this->reporteExternos[$key]['rentabilidad'] = round($data['total'] - $costosTotales, 2);
        }

        foreach (['semanales', 'diarios', 'otros'] as $grupo) {
            $this->$grupo = $this->$grupo->map(function ($item) {
                $gastosAdm = round(($item['porcentaje'] / 100) * $this->totalGastosAdministrativos, 2);
                $costosTotales = $this->calcularCostosTotalesGrupo($item['items'], false, $gastosAdm);

                $item['gastos_adm'] = $gastosAdm;
                //$item['costos_totales'] = $this->calcularCostosTotalesGrupo($item['items'], false, $gastosAdm);
                $item['costos_totales'] = $costosTotales;
                $item['rentabilidad'] = round($item['total'] - $costosTotales, 2);
                return $item;
            });
        }

        $this->calcularResumenFinal();
    }
    // Obtener gastos administrativos
    private function obtenerTotalGastosAdministrativos(): float
    {
        $fecha = Carbon::parse($this->fechaInicio);

        $gasto = GastoAdministrativo::where('periodo_anio', $fecha->year)
            ->where('periodo_mes', $fecha->month)
            ->with(['personal', 'servicios'])
            ->first();

        if (!$gasto) {
            return 0;
        }

        $totalPersonal = $gasto->personal->sum('total');

        $conceptos = ['Alquiler local', 'Internet', 'Motorizados', 'Luz', 'Agua', 'Celulares', 'Materiales de Oficina', 'Sistema', ];

        $totalServicios = $gasto->servicios
            ->whereIn('concepto', $conceptos)
            ->sum('monto');

        return round($totalPersonal + $totalServicios, 2);
    }

    private function calcularCostosTotalesGrupo($items, bool $esExterno, float $gastosAdm): float
    {
        if ($items->isEmpty()) {
            return round($gastosAdm, 2);
        }

        // Si vienen agrupados (talleres), extraemos los items reales
        if (isset($items->first()['items'])) {
            $items = $items->pluck('items')->flatten(1);
        }

        $operativos = $this->calcularCostosOperativos($items, $gastosAdm);

        if ($esExterno) {
            return $operativos['total_operativo'];
        }

        $laborales = $this->calcularCostosLaboralesDesdeItems($items);

        return round($laborales + $operativos['total_operativo'], 2);
    }
    // calcular costos laborales (costos totales)
    private function calcularCostosLaboralesDesdeItems($items): float
    {
        $nombreInspector = $items->first()['inspector'] ?? null;

        if (!$nombreInspector) {
            return 0;
        }

        $inspector = User::where('name', $nombreInspector)->first();
        if (!$inspector) {
            return 0;
        }

        // Asignación familiar
        $asignacionFamiliar = ((int)$inspector->asignacion_familiar === 1) ? 113.00 : 0;

        // Contrato
        $contrato = ContratoTrabajo::where('idUser', $inspector->id)->latest('fechaInicio')->first();

        $sueldoBase = (float) ($contrato->pago ?? 0);
        $sueldosInspector = $sueldoBase + $asignacionFamiliar;

        if ($sueldosInspector <= 0) {
            return 0;
        }

        //$meses = (float) $this->mesesComputables;
        $meses = 6;
        // Gratificación
        $montoGrati = ($sueldosInspector * 0.5 / 6) * $meses;
        $bonoGrati  = $montoGrati * 0.09;
        $gratificacion = ($montoGrati + $bonoGrati) / 6;

        // Essalud
        $essalud = $sueldosInspector * 0.09;

        // CTS
        $gratiCTS   = $sueldosInspector * 0.5 / 6;
        $ctsPeriodo = (($sueldosInspector * 0.5) + $gratiCTS) * $meses / 12;
        $cts        = $ctsPeriodo / 6;

        // Vacaciones
        $vacacion = ($sueldosInspector * 0.5) / 12;

        return round($sueldosInspector + $gratificacion + $essalud + $cts + $vacacion, 2);
    }
    // calcular costos operativos (costos totales)
    private function calcularCostosOperativos($items, float $gastosAdm): array
    {
        $hojas = $items->filter(
            fn($item) =>
            !in_array($item['servicio'], [
                'Chip por deterioro',
                'Desmonte de Cilindro',
                'Activación de chip (Anual)'
            ])
        )->count() * 0.50;

        $chips = $items->filter(
            fn($item) =>
            in_array($item['servicio'], [
                'Conversión a GNV + Chip',
                'Chip por deterioro',
                'Conversión a GNV OVERHUL'
            ])
        )->count() * 20;

        $cofideAnual = $items->filter(
            fn($item) =>
            in_array($item['servicio'], [
                'Revisión anual GNV',
                'Activación de chip (Anual)'
            ])
        )->count() * 2.34;

        $cofideInicial = $items->filter(
            fn($item) =>
            in_array($item['servicio'], [
                'Conversión a GNV',
                'Conversión a GNV + Chip',
                'Pre-inicial GNV'
            ])
        )->count() * 5.46;

        return [
            'gastos_adm' => $gastosAdm,
            'hojas' => round($hojas, 2),
            'chips' => round($chips, 2),
            'servicios_anual_cofide' => round($cofideAnual, 2),
            'servicios_inicial_cofide' => round($cofideInicial, 2),
            'total_operativo' => round($gastosAdm + $hojas + $chips + $cofideAnual + $cofideInicial, 2),
        ];
    }

    private function calcularResumenFinal(): void
    {
        $grupos = [
            $this->reporteExternos, 
            $this->semanales, 
            $this->diarios, 
            $this->otros
        ];

        $totalGastosAdm = 0;
        $totalCostos = 0;
        $totalRentabilidad = 0;

        foreach ($grupos as $grupo) {
            $coleccion = collect($grupo);
            $totalGastosAdm += $coleccion->sum('gastos_adm');
            $totalCostos += $coleccion->sum('costos_totales');
            $totalRentabilidad += $coleccion->sum('rentabilidad');
        }

        $this->resumenFinal = [
            'ingresos' => $this->totalVentas,
            'gastos_adm' => round($totalGastosAdm, 2),
            'costos_totales' => round($totalCostos, 2),
            'rentabilidad' => round($totalRentabilidad, 2),
            'margen' => $this->totalVentas > 0 ? round(($totalRentabilidad / $this->totalVentas) * 100, 2) : 0
        ];
    }


    // FUNCIONES PARA EXTERNOS
    public function procesarExternos($datos)
    {
        $datosFiltrados = $this->aplicarFiltros($datos);
        // Agrupar y ordenar los resultados por inspector
        //$this->aux = $datosFiltrados->groupBy('inspector')->sortBy(fn($item, $key) => $key);
        $aux = $datosFiltrados->groupBy('inspector')->sortBy(fn($items, $inspector) => $inspector);

        // Totales por inspector
        $this->reporteExternos = [];
        $this->totalExternos   = 0;

        foreach ($aux as $inspector => $items) {
            $total = $items->sum('precio');
            $costosTotales = $this->calcularCostosTotalesGrupo($items, true, 0);

            $this->reporteExternos[$inspector] = [
                'inspector' => $inspector,
                'items' => $items,
                'total'     => round($total, 2),
                'porcentaje' => 0,
                'gastos_adm' => 0,
                'costos_totales' => $costosTotales,
            ];

            $this->totalExternos += $total;
        }

        $this->totalExternos = round($this->totalExternos, 2);
    }
    /*public function aplicarFiltros($datos)
    {
        // Inspectores que realmente tienen registros externos
        $inspectoresExternos = $datos
            ->where('externo', 1)
            ->pluck('inspector')
            ->unique()
            ->toArray();

        // Lista de inspectores que realizan servicios de taller y externos
        $inspectoresAdicionales = [
            'Cristhian David Saenz Nuñez',
            'Luis Alberto Esteban Torres',
            'Elvis Alexander Matto Perez',
            'Jhonatan Michael Basilio Soncco',
            'Cristhian Smith Huanay Condor',
            'Javier Alfredo Chevez Parcano',
            'Raul Llata Pacheco',
        ];
        // Servicios especificos que aplican solo para inspectores externos
        $serviciosFiltrados = [
            'Duplicado GNV',
            'Activación de chip (Anual)',
            'Conversión a GNV + Chip',
            'Conversión a GNV',
            'Revisión anual GNV',
            'Desmonte de Cilindro',
            'Chip por deterioro',
            'Pre-inicial GNV',
            'Conversión a GNV OVERHUL',
        ];

        // Inspectores externos → solo servicios filtrados + externo = 1
        $registrosExternos = $datos->filter(fn($item) => in_array($item['inspector'], $inspectoresExternos) && in_array($item['servicio'], $serviciosFiltrados) && $item['externo'] == 1);

        // Inspectores adicionales → cualquier servicio + externo = 1
        $registrosAdicionalesFiltrados = $datos->filter(
            fn($item) =>
            in_array($item['inspector'], $inspectoresAdicionales) && $item['externo'] == 1
        )->reject(
            fn($item) =>
            $registrosExternos->contains('id', $item['id'])
        );

        // Combinar ambas colecciones
        return $registrosExternos->merge($registrosAdicionalesFiltrados)->unique('id');
    }*/
    public function aplicarFiltros($datos)
    {
        return $datos->where('externo', 1)->unique('id');
    }



    // FUNCIONES PARA TALLERES
    public function procesarTalleres($datos)
    {
        $datosFiltrados = $this->filtrarExternos($datos);
        // Agrupar por taller y filtrar por inspectores designados
        $this->asistir = $this->agruparTalleresConsolidar($datosFiltrados);
        // Clasificar por frecuencia
        $this->clasificarPorFrecuencia($this->asistir);

        $this->totalTalleres = collect($this->semanales)->sum('total') + collect($this->diarios)->sum('total') + collect($this->otros)->sum('total');
        $this->totalTalleres = round($this->totalTalleres, 2);
    }
    private function filtrarExternos($tabla)
    {
        return $tabla->filter(function ($item) {
            return is_null($item['externo']) || $item['externo'] == 0;
        })->values();
    }
    private function agruparTalleresConsolidar($tabla)
    {
        // Definir mapa de consolidaciones
        $consolidaciones = [
            'AUTOTRONICA JOEL CARS' => 'AUTOTRONICA JOEL CARS E.I.R.L. - II',
            //'UNIGAS CONVERSIONES S.A.C.' => 'UNIGAS HOME S.A.C.',
            'AUTOGAS GREEN CAR E.I.R.L. - II' => 'WILTON MOTORS E.I.R.L -II'
        ];

        return $tabla
            ->groupBy('taller')
            ->map(function ($items) {
                return [
                    'taller' => $items->first()['taller'],
                    'encargado' => $items->first()['representante'] ?? null,
                    'items' => $items,
                    'total' => $items->sum('precio'),
                    'porcentaje' => 0,
                    'gastos_adm' => 0,
                    'costos_totales' => 0,
                ];
            })
            ->filter(fn($data) => $data['total'] > 0)
            ->groupBy(function ($item) use ($consolidaciones) {
                return $consolidaciones[$item['taller']] ?? $item['taller'];
            })
            ->map(function ($groupedItems, $taller) {
                return [
                    'taller' => $taller,
                    'encargado' => $groupedItems->first()['encargado'],
                    'items' => $groupedItems,
                    'total' => $groupedItems->sum('total'),
                    'porcentaje' => 0,
                    'gastos_adm' => 0,
                    'costos_totales' => 0,
                ];
            })
            ->filter(fn($data) => $data['total'] > 0)
            ->sortBy('taller')
            ->values();
    }
    private function clasificarPorFrecuencia($talleres)
    {
        $this->semanales = collect();
        $this->diarios   = collect();
        $this->otros     = collect();

        foreach ($talleres as $item) {
            $taller = Taller::where('nombre', $item['taller'])->first();

            if (!$taller) {
                $this->otros->push($item);
                continue;
            }

            if ($taller->es_semanal == 1) {
                $this->semanales->push($item);
            } elseif ($taller->es_diario == 1) {
                $this->diarios->push($item);
            } else {
                $this->otros->push($item);
            }
        }
    }
}
