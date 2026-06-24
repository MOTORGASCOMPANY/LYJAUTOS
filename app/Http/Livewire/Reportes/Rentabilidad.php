<?php

namespace App\Http\Livewire\Reportes;

use App\Exports\ReporteRentabilidadExport;
use App\Models\ContratoTrabajo;
use App\Models\GastoAdministrativo;
use App\Models\Taller;
use App\Models\User;
use App\Services\DataService;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class Rentabilidad extends Component
{
    // VARIABLES PARA FILTROS
    public $talleres, $inspectores, $fechaInicio, $fechaFin, $servicio ;
    // VARIABLES PARA BUSCAR DENTRO DE LOS FILTROS
    public $taller = [], $ins = [];

    public $tabla2; // GUARDA TODA LA DATA

    public $certificacionesPorTaller = []; // N° CERTIFICACIONES
    public $ingresosPorTaller = []; // INGRESOS TOTALES
    public $costosPorTaller; // COSTOS TOTALES
    public $rentabilidadPorTaller; // RENTABILIDAD
    public $ingresosPorServicio = []; // INGRESOS POR TIPO SERVICIO
    
    // VARIABLES PARA AJUSTAR O ACTUALIZAR INGRESOS Y COSTOS TOTALES
    public $ajustarIngresos = false, $ajustarCostos = false;

    // VARIABLES PARA SUELDO DE INSPECTOR Y GASTOS ADMINISTRATIVOS
    public $sueldoInspector = 0;
    public $gastosAdministrativos = 0;

    public $mesesComputables = 6;
    //public $grati = 0;

    protected $dataService;

    public $modo = 'taller'; // taller | externo

    protected $listeners = ['exportarExcel'];

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
        $this->inspectores = User::role(['inspector', 'supervisor'])
            ->where('id', '!=', 201)
            ->orderBy('name')
            ->get();
        $this->talleres = Taller::orderBy('nombre')->get();
    }

    private function getGroupKey()
    {
        return $this->modo === 'externo' ? 'inspector' : 'taller';
    }

    public function updatedModo()
    {
        $this->taller = [];
        $this->ins = [];
        $this->tabla2 = null;
    }

    public function procesar()
    {
        $this->validate();
        /*$tabla = $this->generaData();
        $importados = $this->cargaServiciosGasolution();
        $tabla = $tabla->map(function ($item) {
            $item['placa'] = trim($item['placa']);
            $item['inspector'] = trim($item['inspector']);
            $item['taller'] = trim($item['taller']);
            return $item;
        });
        $importados = $importados->map(function ($item) {
            $item['placa'] = trim($item['placa']);
            $item['inspector'] = trim($item['inspector']);
            $item['taller'] = trim($item['taller']);
            return $item;
        });
        $diferencias = $this->encontrarDiferenciaPorPlaca($importados, $tabla);
        $data = $tabla->merge($diferencias);*/

        $data = $this->dataService->procesar($this->ins, $this->taller, $this->servicio, $this->fechaInicio, $this->fechaFin);

        /*/ Aplicamos filtro `externo` recorre cada elemento y solo conserva (externo = NULL o 0)
        $this->tabla2 = $data->filter(function ($item) {
            return is_null($item['externo']) || $item['externo'] == 0;
        })->values(); // Reindexar la colección*/

        $this->tabla2 = $data->filter(function ($item) {
            if ($this->modo === 'externo') {
                return $item['externo'] == 1;
            }

            return is_null($item['externo']) || $item['externo'] == 0;
        })->values();

        $this->generarReporte();
    } 

    public function generarReporte()
    {
        
        $this->certificacionesPorTaller = $this->calcularCertificacionesPorTaller(); // N° CERTIFICACIONES
        $this->ingresosPorTaller = $this->calcularIngresosPorTaller(); // INGRESOS TOTALES        
        $this->costosPorTaller = $this->calcularCostosPorTaller(); // COSTOS TOTALES
        $this->rentabilidadPorTaller = $this->calcularRentabilidadPorTaller(); // RENTABILIDAD
        $this->ingresosPorServicio = $this->calcularIngresosPorServicio(); // INGRESOS POR TIPO SERVICIO
    }

    public function render()
    {
        return view('livewire.reportes.rentabilidad', [
            'certificacionesPorTaller' => $this->certificacionesPorTaller, // N° CERTIFICACIONES
            'ingresosPorTaller' => $this->ingresosPorTaller, // INGRESOS TOTALES 
            'costosPorTaller' => $this->costosPorTaller, // COSTOS TOTALES
            'rentabilidadPorTaller' => $this->rentabilidadPorTaller, // RENTABILIDAD
            'ingresosPorServicio' => $this->ingresosPorServicio, // INGRESOS POR TIPO SERVICIO
        ]);
    }

    // TABLA N° CERTIFICACIONES REALIZADAS POR TALLER
    private function calcularCertificacionesPorTaller()
    {
        /*return $this->tabla2->groupBy('taller')->map(function ($items, $taller) {
            return [
                'taller' => $taller,
                'total_certificaciones' => $items->count()
            ];
        })->sortByDesc('total_certificaciones')->values()->toArray();*/
        $groupKey = $this->getGroupKey();

        return $this->tabla2->groupBy($groupKey)->map(function ($items, $key) use ($groupKey) {
            return [
                $groupKey => $key,
                'total_certificaciones' => $items->count()
            ];
        })->sortByDesc('total_certificaciones')->values()->toArray();

    }

    // TABLA INGRESOS TOTALES POR TALLER
    private function calcularIngresosPorTaller()
    {
        /*return $this->tabla2->groupBy('taller')->map(function ($items, $taller) {
            $ingresosTotales = $items->sum('precio');
            $igvFactor  = 0.82;

            if ($this->ajustarIngresos) {
                $ingresosTotales = round($ingresosTotales * $igvFactor, 2);
            }

            return [
                'taller' => $taller,
                'ingresos_totales' => $ingresosTotales
            ];
        })->sortByDesc('ingresos_totales')->values()->toArray();*/
        $groupKey = $this->getGroupKey();

        return $this->tabla2->groupBy($groupKey)->map(function ($items, $key) use ($groupKey) {
            $ingresosTotales = $items->sum('precio');

            if ($this->ajustarIngresos) {
                $ingresosTotales = round($ingresosTotales * 0.82, 2);
            }

            return [
                $groupKey => $key,
                'ingresos_totales' => $ingresosTotales
            ];
        })->sortByDesc('ingresos_totales')->values()->toArray();

    }

    // TABLA COSTOS TOTALES POR TALLER
    /*private function calcularCostosPorTaller()
    {
        return $this->tabla2->groupBy('taller')->map(function ($items, $taller) {
            //$sueldosInspector = $this->sueldoInspector;
            $sueldosInspector = floatval($this->sueldoInspector);            
            $gratificacion = round(($sueldosInspector / 2) * 0.09, 2);
            //$gastosAdm = $this->gastosAdministrativos;
            $gastosAdm = floatval($this->gastosAdministrativos);
            $essalud = round($sueldosInspector * 0.09, 2);

            //$grati = $this->grati; // Sexta parte de la gratificación
            //$meses = $this->mesesComputables; // Meses computables
            $grati = floatval($this->grati); // sexta parte
            $meses = floatval($this->mesesComputables); // meses computables

            //$cts = round((($sueldosInspector / 2) + ($gratificacion / 6)) / 12 * 6, 2);
            $cts = round((($sueldosInspector / 2) + $grati) / 12 * $meses, 2);


            // Calcular Costo de hojas
            $hojas = $items->filter(function ($item) {
                return !in_array($item['servicio'], ['Chip por deterioro', 'Desmonte de Cilindro', 'Activación de chip (Anual)']);
            })->count() * 0.50;
            // Calcular Costo de chips
            $chips = $items->filter(function ($item) {
                return in_array($item['servicio'], ['Conversión a GNV + Chip', 'Chip por deterioro', 'Conversión a GNV OVERHUL']);
            })->count() * 20;
            // Calcular Costo de servicios anual cofide
            $serviciosAnualCofide = $items->filter(function ($item) {
                return in_array($item['servicio'], ['Revisión anual GNV', 'Activación de chip (Anual)']);
            })->count() * 2.34;
            // Calcular Costo inicial cofide
            $serviciosInicialCofide = $items->filter(function ($item) {
                return in_array($item['servicio'], ['Conversión a GNV', 'Conversión a GNV + Chip', 'Pre-inicial GNV']);
            })->count() * 5.46;

            $costosTotales = $sueldosInspector + $gratificacion + $hojas + $chips + $serviciosAnualCofide + $serviciosInicialCofide + $gastosAdm;

            if ($this->ajustarCostos) {
                $costosTotales += $essalud + $cts;
            }

            return [
                'taller' => $taller,
                'sueldos_inspector' => $sueldosInspector,
                'gratificacion' => $gratificacion,
                'essalud' => $this->ajustarCostos ? $essalud : 0,
                'cts' => $this->ajustarCostos ? $cts : 0,
                'gastos_adm' => $gastosAdm,
                'hojas' => $hojas,
                'chips' => $chips,
                'servicios_anual_cofide' => $serviciosAnualCofide,
                'servicios_inicial_cofide' => $serviciosInicialCofide,
                'costos_totales' => $costosTotales
            ];
        })->sortByDesc('costos_totales')->values()->toArray();
    }*/
    
    private function calcularCostosPorTaller()
    {
        $groupKey = $this->getGroupKey();

        $fecha = Carbon::parse($this->fechaInicio);
        $anio  = $fecha->year;
        $mes   = $fecha->month;
        $totalGastosAdministrativos = 0;
        $gastoAdministrativo = GastoAdministrativo::where('periodo_anio', $anio)
            ->where('periodo_mes', $mes)
            ->with(['personal', 'servicios'])
            ->first();

        if ($gastoAdministrativo) {
            $totalPersonal = $gastoAdministrativo->personal->sum('total');

            $conceptosServiciosAdm = [
                'Alquiler local',
                'Internet',
                'Motorizados',
                'Luz',
                'Agua',
                'Celulares',
                'Materiales de Oficina',
                'Sistema',
            ];

            $totalServiciosAdm = $gastoAdministrativo->servicios
                ->whereIn('concepto', $conceptosServiciosAdm)
                ->sum('monto');

            $totalGastosAdministrativos = $totalPersonal + $totalServiciosAdm;
        }

        return $this->tabla2->groupBy($groupKey)->map(function ($items, $key) use ($groupKey, $totalGastosAdministrativos) {

            $esExterno = $this->modo === 'externo';

            // ID del inspector
            $nombreInspector = $items->first()['inspector'];            
            $inspector = User::where('name', $nombreInspector)->first();
            $inspectorId = $inspector?->id;

            // Asignacion familiar
            $tieneAsignacion = (int) ($inspector?->asignacion_familiar ?? 0);
            $asignacionFamiliar = $tieneAsignacion === 1 ? 113.00 : 0;

            // Contrato del inspector
            $contrato = ContratoTrabajo::where('idUser', $inspectorId)->latest('fechaInicio')->first();
            // Sueldo automático desde contrato
            $sueldoBase = floatval($contrato?->pago ?? 0);
            // Sueldo de inspector + asignacion familiar en caso corresponda
            $sueldosInspector = $sueldoBase + $asignacionFamiliar;

            $meses = floatval($this->mesesComputables);

            // ((50% de sueldo / 6) * mesescomputables ) + asignacion
            $montogratificacion = round(($sueldosInspector * 0.5 / 6) * $meses, 2);
            // montogratificacion * 0.09
            $bonogratificacion = round($montogratificacion * 0.09, 2);
            // periodo completo (6meses) / 6 mensual por rentabilidad
            $gratificacion = ($montogratificacion + $bonogratificacion) / 6;            

            // essalud 9% de sueldo
            $essalud = round($sueldosInspector * 0.09, 2);

            // grati para cts es : 1/6 del 50% de sueldo
            $gratiCTS = $sueldosInspector * 0.5 / 6;
            // cts periodo = ( 50% de sueldo + grati) x meses computables / 12
            $ctsPeriodo  = round((($sueldosInspector * 0.5) + $gratiCTS) * $meses / 12, 2);
            // cts mensual por rentabilidad
            $cts = round(($ctsPeriodo  / 6) ,2);

            // vacaciones 50% de sueldo / 12
            $vacacion = round((($sueldosInspector * 0.5) / 12), 2);


            $costosLaborales = $sueldosInspector + $gratificacion + $essalud + $cts + $vacacion;

            // Gastos administrativos
            $porcentajeGastosAdm = floatval($this->gastosAdministrativos);
            $gastosAdm = round($totalGastosAdministrativos * ($porcentajeGastosAdm / 100), 2);

            // Costos variables
            $hojas = $items->filter(fn ($item) =>
                !in_array($item['servicio'], [
                    'Chip por deterioro',
                    'Desmonte de Cilindro',
                    'Activación de chip (Anual)'
                ])
            )->count() * 0.50;
            $chips = $items->filter(fn ($item) =>
                in_array($item['servicio'], [
                    'Conversión a GNV + Chip',
                    'Chip por deterioro',
                    'Conversión a GNV OVERHUL'
                ])
            )->count() * 20;
            $serviciosAnualCofide = $items->filter(fn ($item) =>
                in_array($item['servicio'], [
                    'Revisión anual GNV',
                    'Activación de chip (Anual)'
                ])
            )->count() * 2.34;
            $serviciosInicialCofide = $items->filter(fn ($item) =>
                in_array($item['servicio'], [
                    'Conversión a GNV',
                    'Conversión a GNV + Chip',
                    'Pre-inicial GNV'
                ])
            )->count() * 5.46;

            $costosOperativos = $hojas + $chips + $serviciosAnualCofide + $serviciosInicialCofide + $gastosAdm;

            /*$costosTotales = $sueldosInspector
                + $gratificacion
                + $essalud
                + $cts
                + $vacacion
                + $hojas
                + $chips
                + $serviciosAnualCofide
                + $serviciosInicialCofide
                + $gastosAdm;*/
            $costosTotales = $esExterno ? $costosOperativos : ($costosLaborales + $costosOperativos);

            /*if ($this->ajustarCostos) {
                $costosTotales += $essalud + $cts;
            }*/

            return [
                $groupKey => $key,
                //laborales
                // 'sueldos_inspector' => $sueldosInspector,
                // 'gratificacion' => $gratificacion,
                // 'essalud' => $essalud,
                // 'cts' => $cts,
                // 'vacacion' => $vacacion,
                'sueldos_inspector' => $esExterno ? 0 : $sueldosInspector,
                'gratificacion'     => $esExterno ? 0 : $gratificacion,
                'essalud'           => $esExterno ? 0 : $essalud,
                'cts'               => $esExterno ? 0 : $cts,
                'vacacion'          => $esExterno ? 0 : $vacacion,

                // operativos
                'gastos_adm' => $gastosAdm,
                'hojas' => $hojas,
                'chips' => $chips,
                'servicios_anual_cofide' => $serviciosAnualCofide,
                'servicios_inicial_cofide' => $serviciosInicialCofide,
                
                'costos_totales' => round($costosTotales, 2),
            ];

        })->sortByDesc('costos_totales')->values()->toArray();
    }

    public function updated($property, $value = null)
    {
        if (in_array($property, [
            'ajustarIngresos',
            'ajustarCostos',
            'sueldoInspector',
            'mesesComputables'
        ])) {
            $this->generarReporte();
        }

        if ($property === 'gastosAdministrativos') {
            if (!is_numeric($value)) {
                $this->addError('gastosAdministrativos', 'Debe ser un número válido.');
                return;
            }
            $this->resetErrorBag('gastosAdministrativos');
            $this->gastosAdministrativos = floatval($value);
            $this->generarReporte();
        }
    }

    // TABLA RENTABILIDAD POR TALLER
    private function calcularRentabilidadPorTaller()
    {
        /*$ingresos = collect($this->ingresosPorTaller)->keyBy('taller');
        $costos = collect($this->costosPorTaller)->keyBy('taller');

        $talleres = $ingresos->keys()->merge($costos->keys())->unique();

        return $talleres->map(function ($taller) use ($ingresos, $costos) {
            $ingresosTotales = $ingresos->get($taller)['ingresos_totales'] ?? 0;
            $costosTotales = $costos->get($taller)['costos_totales'] ?? 0;
            $rentabilidad = round($ingresosTotales - $costosTotales, 2);

            return [
                'taller' => $taller,
                'ingresos_totales' => $ingresosTotales,
                'costos_totales' => $costosTotales,
                'rentabilidad' => $rentabilidad
            ];
        })->sortByDesc('rentabilidad')->values()->toArray();*/
        $groupKey = $this->getGroupKey();

        $ingresos = collect($this->ingresosPorTaller)->keyBy($groupKey);
        $costos   = collect($this->costosPorTaller)->keyBy($groupKey);

        $grupos = $ingresos->keys()->merge($costos->keys())->unique();

        return $grupos->map(function ($key) use ($ingresos, $costos, $groupKey) {

            $ingresosTotales = $ingresos->get($key)['ingresos_totales'] ?? 0;
            $costosTotales   = $costos->get($key)['costos_totales'] ?? 0;

            $rentabilidad = round($ingresosTotales - $costosTotales, 2);

            return [
                $groupKey => $key,
                'ingresos_totales' => $ingresosTotales,
                'costos_totales' => $costosTotales,
                'rentabilidad' => $rentabilidad,
            ];

        })->sortByDesc('rentabilidad')->values()->toArray();
    }

    // TABLA INGRESOS POR TIPO DE SERVICIO POR TALLER
    private function calcularIngresosPorServicio()
    {
        /*/ Verificación inicial de los datos
        $tabla2 = $this->tabla2->map(function ($item) {
            return [
                'taller' => $item['taller'],
                'servicio' => $item['servicio'],
                'precio' => $item['precio'],
            ];
        });

        // Agrupamos por taller y luego por servicio
        $ingresosPorServicio = $tabla2->groupBy('taller')->map(function ($items, $taller) {
            return $items->groupBy('servicio')->map(function ($items, $servicio) use ($taller) {
                return [
                    'taller' => $taller,
                    'servicio' => $servicio,
                    'cantidad' => $items->count(),
                    'ingresos_totales' => $items->sum('precio')
                ];
            })->values()->toArray();
        })->flatten(1)->sortByDesc('ingresos_totales')->values()->toArray();
        //dd($ingresosPorServicio);

        return $ingresosPorServicio;*/
        $groupKey = $this->getGroupKey();

        // Normalizamos los datos
        $tabla2 = $this->tabla2->map(function ($item) use ($groupKey) {
            return [
                $groupKey => $item[$groupKey],
                'servicio' => $item['servicio'],
                'precio' => $item['precio'],
            ];
        });

        // Agrupar por Taller / Inspector y luego por Servicio
        return $tabla2
            ->groupBy($groupKey)
            ->map(function ($items, $groupValue) use ($groupKey) {

                return $items
                    ->groupBy('servicio')
                    ->map(function ($items, $servicio) use ($groupKey, $groupValue) {

                        return [
                            $groupKey => $groupValue,
                            'servicio' => $servicio,
                            'cantidad' => $items->count(),
                            'ingresos_totales' => $items->sum('precio'),
                        ];

                    })->values();

            })
            ->flatten(1)
            ->sortByDesc('ingresos_totales')
            ->values()
            ->toArray();

    }

    //EXPORTAR A EXCELL
    public function exportarExcel($data)
    {
        //dd($data);
        $fecha = $this->fechaInicio . 'al' . $this->fechaFin;
        return Excel::download(new ReporteRentabilidadExport($data), 'Rentabilidad del ' . $fecha . '.xlsx');
    }
    
}




/*/ CARGAMOS DATA DE CERTIFICACION, CERTIFICADOS_PENDIENTES Y DESMONTES
    public function generaData()
    {
        $tabla = new Collection();
        //TODO CERTIFICACIONES:
        $certificaciones = Certificacion::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            //->IdTipoServicio($this->servicio)
            ->rangoFecha($this->fechaInicio, $this->fechaFin)
            ->whereIn('pagado', [0, 2])
            ->whereNotIn('estado', [2])
            ->where(function ($query) {
                $query->whereNull('placaantigua')
                      ->orWhere('placaantigua', 0);
            })
            ->get();

        //TODO CER-PENDIENTES:
        $cerPendiente = CertificacionPendiente::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            //->IdTipoServicios($this->servicio)
            ->rangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();
        // DESMONTES
        $desmontes = Desmontes::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            //->IdTipoServicios($this->servicio)
            ->rangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();
        // CERT TEMPORALES
        $cerTemp = CertificacionTemporal::idTalleres($this->taller)
            ->IdInspectores($this->ins)
            ->rangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();
            
        //unificando certificaciones     
        foreach ($certificaciones as $certi) {
            //modelo preliminar
            $data = [
                "id" => $certi->id,
                "placa" => $certi->Vehiculo->placa,
                "taller" => $certi->Taller->nombre,
                "representante" => $certi->Taller->representante,
                "inspector" => $certi->Inspector->name,
                "servicio" => $certi->Servicio->tipoServicio->descripcion,
                "num_hoja" => $certi->NumHoja,
                "ubi_hoja" => $certi->UbicacionHoja,
                "precio" => $certi->precio,
                "pagado" => $certi->pagado,
                "estado" => $certi->estado,
                "externo" => $certi->externo,
                "tipo_modelo" => $certi::class,
                "fecha" => $certi->created_at,

            ];
            $tabla->push($data);
        }

        foreach ($cerPendiente as $cert_pend) {
            //modelo preliminar
            $data = [
                "id" => $cert_pend->id,
                "placa" => $cert_pend->Vehiculo->placa,
                "taller" => $cert_pend->Taller->nombre,
                "representante" => $cert_pend->Taller->representante,
                "inspector" => $cert_pend->Inspector->name,
                "servicio" => 'Activación de chip (Anual)', // es ese tipo de servicio por defecto
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $cert_pend->precio,
                "pagado" => $cert_pend->pagado,
                "estado" => $cert_pend->estado,
                "externo" => $cert_pend->externo,
                "tipo_modelo" => $cert_pend::class,
                "fecha" => $cert_pend->created_at,
            ];
            $tabla->push($data);
        }

        foreach ($desmontes as $des) {
            $data = [
                "id" => $des->id,
                "placa" => $des->placa,
                "taller" => $des->Taller->nombre,
                "representante" => $des->Taller->representante,
                "inspector" => $des->Inspector->name,
                "servicio" => $des->Servicio->tipoServicio->descripcion,
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $des->precio,
                "pagado" => $des->pagado,
                "estado" => $des->estado,
                "externo" => $des->externo,
                "tipo_modelo" => $des::class,
                "fecha" => $des->created_at,
            ];
            $tabla->push($data);
        }

        foreach ($cerTemp as $ctemp) {
            $data = [
                "id" => $ctemp->id,
                "placa" => $ctemp->placa,
                "taller" => $ctemp->Taller->nombre,
                "representante" => $ctemp->Taller->representante,
                "inspector" => $ctemp->Inspector->name,
                "servicio" => $ctemp->Servicio->tipoServicio->descripcion,
                "num_hoja" => $ctemp->numSerie,
                "ubi_hoja" => Null,
                "precio" => $ctemp->precio,
                "pagado" => $ctemp->pagado,
                "estado" => $ctemp->estado,
                "externo" => $ctemp->externo,
                "tipo_modelo" => $ctemp::class,
                "fecha" => $ctemp->created_at,
                "updated_at" => $ctemp->updated_at,
            ];
            $tabla->push($data);
        }
        return $tabla;
    }
    // DIFERENCIAS ENTRE LISTA 1 (generaData) Y LISTA 2 (cargaServiciosGasolution)
    public function encontrarDiferenciaPorPlaca($lista1, $lista2)
    {
        $diferencias = [];

        foreach ($lista1 as $elemento1) {
            $placa1 = $elemento1['placa'];
            $inspector1 = $elemento1['inspector'];
            $servicio1 = $elemento1['servicio'];
            $taller1 = $elemento1['taller'];
            $encontrado = false;

            foreach ($lista2 as $elemento2) {
                $placa2 = $elemento2['placa'];
                $inspector2 = $elemento2['inspector'];
                $servicio2 = $elemento2['servicio'];
                $taller2 = $elemento2['taller'];

                if ($placa1 === $placa2 && $inspector1 === $inspector2 && $taller1 === $taller2) {
                    if (
                        ($elemento2['tipo_modelo'] == 'App\Models\CertificacionPendiente' && $servicio1 == 'Revisión anual GNV') ||
                        ($servicio2 == 'Conversión a GNV + Chip' && $servicio1 == 'Conversión a GNV') ||
                        ($servicio2 == 'Pre-inicial GNV' && $servicio1 == 'Conversión a GNV') ||
                        ($servicio2 == 'Conversión a GNV OVERHUL' && $servicio1 == 'Conversión a GNV')
                    ) {
                        $encontrado = true;
                        break;
                    } else if ($servicio1 === $servicio2) {
                        $encontrado = true;
                        break;
                    }
                }
            }

            if (!$encontrado) {
                $diferencias[] = $elemento1;
            }
        }

        return $diferencias;
    }
    // CARGAMOS DATA DE SERVICIOS_IMPORTADOS
    public function cargaServiciosGasolution()
    {
        $disc = new Collection();
        $dis = ServiciosImportados::Talleres($this->taller)
            ->Inspectores($this->ins)
            //->TipoServicio($this->servicio)
            ->RangoFecha($this->fechaInicio, $this->fechaFin)
            ->get();

        foreach ($dis as $registro) {
            $data = [
                "id" => $registro->id,
                "placa" => $registro->placa,
                "taller" => $registro->taller,
                "representante" => $registro->representante ?? null,
                "inspector" => $registro->certificador,
                "servicio" => $registro->TipoServicio->descripcion,
                "num_hoja" => Null,
                "ubi_hoja" => Null,
                "precio" => $registro->precio,
                "pagado" => $registro->pagado,
                "estado" => $registro->estado,
                "externo" => $registro->externo,
                "tipo_modelo" => $registro::class,
                "fecha" => $registro->fecha,
            ];
            $disc->push($data);
        }
        return $disc;
    }
*/