<?php

namespace App\Http\Livewire\Reportes;

use App\Exports\ReporteFotosExport;
use App\Models\Expediente;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReporteFotosPorInspector extends Component
{
    use WithPagination;

    public $orderField = 'id', $orderDirection = 'desc', $perPage = 10;

    public $tiposGNV = [1, 2, 7, 10, 14];
    public $tiposGLP = [3, 4];
    public $imagenesGNV = [1, 2, 3, 4, 5, 6, 7, 8, 9];
    public $imagenesGLP = [1, 2, 3, 4, 5, 6, 8, 9, 10, 11];

    public $inspectores, $ins;
    public $fecIni, $fecFin;
    public $estado = '';

    public $openModal = false;
    //public $detalles = [];
    public array $detalles = [
        'inspector' => '',
        'gnv' => [],
        'gnv_sin_fotos' => [],
        'glp' => [],
        'glp_sin_fotos' => [],
    ];


    public function mount()
    {
        $this->inspectores = User::role(['inspector', 'supervisor'])->where('id', '!=', Auth::id())->orderBy('name')->get();
    }
    public function order($field)
    {
        if ($this->orderField === $field) {
            $this->orderDirection = $this->orderDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->orderField = $field;
            $this->orderDirection = 'asc';
        }
    }

    /*private function getRequeridas($tipoServicioId)
    {
        if (in_array($tipoServicioId, $this->tiposGNV)) {

            // caso especial servicio 7
            if ($tipoServicioId == 7) {
                return array_values(array_diff($this->imagenesGNV, [1, 2]));
            }

            return $this->imagenesGNV;
        }

        if (in_array($tipoServicioId, $this->tiposGLP)) {
            return $this->imagenesGLP;
        }

        return [];
    }

    private function generarResumen()
    {
        $query = Expediente::with(['Inspector', 'Servicio.tipoServicio', 'Archivos'])
            ->when($this->ins, fn($q) => $q->where('usuario_idusuario', $this->ins))
            ->when(
                $this->fecIni && $this->fecFin,
                fn($q) =>
                $q->whereBetween('created_at', [
                    $this->fecIni . " 00:00:00",
                    $this->fecFin . " 23:59:59"
                ])
            )
            ->when(
                $this->fecIni && !$this->fecFin,
                fn($q) =>
                $q->whereDate('created_at', ">=", $this->fecIni)
            )
            ->when(
                !$this->fecIni && $this->fecFin,
                fn($q) =>
                $q->whereDate('created_at', "<=", $this->fecFin)
            )
            ->get();

        $resumen = [];

        foreach ($query as $exp) {

            $inspectorId = $exp->usuario_idusuario;
            $inspectorNombre = $exp->Inspector->name ?? 'SIN NOMBRE';

            if (!isset($resumen[$inspectorId])) {
                $resumen[$inspectorId] = [
                    'inspector'   => $inspectorNombre,

                    'gnv_comp'    => 0,
                    'gnv_incomp'  => 0,
                    'gnv_sin_fotos'  => 0,   // NUEVO

                    'glp_comp'    => 0,
                    'glp_incomp'  => 0,
                    'glp_sin_fotos'  => 0,   // NUEVO

                    'detalles_gnv' => [],
                    'detalles_gnv_sin_fotos' => [],

                    'detalles_glp' => [],
                    'detalles_glp_sin_fotos' => [],
                ];
            }

            $tipoServicioId = $exp->Servicio->tipoServicio->id ?? null;

            

            $imagenesValidas = $exp->Archivos->filter(
                fn($a) =>
                in_array(strtolower($a->extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])
            );

            $cantidadFotos = $imagenesValidas->count();

            $requeridas = $this->getRequeridas($tipoServicioId);

            $presentes = $imagenesValidas->pluck('tipo_imagen_id')->toArray();

            $faltantes = array_diff($requeridas, $presentes);

            $completo = count($faltantes) === 0;

            // G N V
            if (in_array($tipoServicioId, $this->tiposGNV)) {

                // Expediente sin fotos
                if ($cantidadFotos === 0) {
                    $resumen[$inspectorId]['gnv_sin_fotos']++;
                }

                if ($completo) {
                    $resumen[$inspectorId]['gnv_comp']++;
                } else {
                    $resumen[$inspectorId]['gnv_incomp']++;

                    // Guardar detalles para modal
                    if ($cantidadFotos === 0) {
                        $resumen[$inspectorId]['detalles_gnv_sin_fotos'][] = [
                            'placa'       => $exp->placa,
                            'certificado' => $exp->certificado,
                        ];
                    } else {
                        $resumen[$inspectorId]['detalles_gnv'][] = [
                            'placa'       => $exp->placa,
                            'certificado' => $exp->certificado,
                        ];
                    }

                }
            }

            // G L P
            if (in_array($tipoServicioId, $this->tiposGLP)) {

                // Expediente sin fotos
                if ($cantidadFotos === 0) {
                    $resumen[$inspectorId]['glp_sin_fotos']++;
                }

                if ($completo) {
                    $resumen[$inspectorId]['glp_comp']++;
                } else {
                    $resumen[$inspectorId]['glp_incomp']++;

                    if ($cantidadFotos === 0) {
                        $resumen[$inspectorId]['detalles_glp_sin_fotos'][] = [
                            'placa'       => $exp->placa,
                            'certificado' => $exp->certificado,
                        ];
                    } else {
                        $resumen[$inspectorId]['detalles_glp'][] = [
                            'placa'       => $exp->placa,
                            'certificado' => $exp->certificado,
                        ];
                    }

                }
            }

        }

        // Calcular porcentajes
        foreach ($resumen as &$r) {
            $totalGNV = $r['gnv_comp'] + $r['gnv_incomp'];
            $totalGLP = $r['glp_comp'] + $r['glp_incomp'];

            $r['gnv_pct'] = $totalGNV > 0 ? round(($r['gnv_comp'] / $totalGNV) * 100, 1) : 0;
            $r['glp_pct'] = $totalGLP > 0 ? round(($r['glp_comp'] / $totalGLP) * 100, 1) : 0;

            // Nuevas columnas
            $r['gnv_tot'] = $totalGNV;
            $r['glp_tot'] = $totalGLP;
        }

        return collect($resumen)
            ->sortBy('inspector')
            ->values();
    }*/

    private function getRequeridas($tipoServicioId)
    {
        if (in_array($tipoServicioId, $this->tiposGNV)) {
            return $tipoServicioId == 7
                ? array_values(array_diff($this->imagenesGNV, [1, 2]))
                : $this->imagenesGNV;
        }

        if (in_array($tipoServicioId, $this->tiposGLP)) {
            return $this->imagenesGLP;
        }

        return [];
    }

    private function generarResumen()
    {
        $expedientes = Expediente::with(['Inspector', 'Servicio.tipoServicio', 'Archivos'])
            ->when($this->ins, fn($q) => $q->where('usuario_idusuario', $this->ins))
            ->when($this->fecIni && $this->fecFin, fn($q) =>
                $q->whereBetween('created_at', [
                    $this->fecIni . ' 00:00:00',
                    $this->fecFin . ' 23:59:59'
                ])
            )
            ->where(function ($q) {
                // Expedientes SIN certificación
                $q->whereDoesntHave('certificacionesExpediente')
                // Expedientes CON certificación NO anulada
                ->orWhereHas('certificacionesExpediente.certificacion', function ($c) {
                    $c->where('estado', '!=', 2);
                });
            })
            ->get();

        $resumen = [];

        foreach ($expedientes as $exp) {

            $id = $exp->usuario_idusuario;
            $nombre = $exp->Inspector->name ?? 'SIN NOMBRE';

            if (!isset($resumen[$id])) {
                $resumen[$id] = [
                    'inspector' => $nombre,

                    'gnv_comp' => 0,
                    'gnv_incomp' => 0,
                    'gnv_sin_fotos' => 0,

                    'glp_comp' => 0,
                    'glp_incomp' => 0,
                    'glp_sin_fotos' => 0,

                    'detalles_gnv' => [],
                    'detalles_gnv_sin_fotos' => [],

                    'detalles_glp' => [],
                    'detalles_glp_sin_fotos' => [],
                ];
            }

            $tipoServicioId = $exp->Servicio->tipoServicio->id ?? null;

            $imagenes = $exp->Archivos->filter(fn($a) =>
                in_array(strtolower($a->extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])
            );

            $cantidadFotos = $imagenes->count();
            $requeridas = $this->getRequeridas($tipoServicioId);
            $presentes = $imagenes->pluck('tipo_imagen_id')->toArray();
            $completo = count(array_diff($requeridas, $presentes)) === 0;

            // GNV
            if (in_array($tipoServicioId, $this->tiposGNV)) {
                if ($cantidadFotos === 0) {
                    $resumen[$id]['gnv_sin_fotos']++;
                    $resumen[$id]['detalles_gnv_sin_fotos'][] = [
                        'placa' => $exp->placa,
                        'certificado' => $exp->certificado,
                    ];
                }

                $completo
                    ? $resumen[$id]['gnv_comp']++
                    : $resumen[$id]['gnv_incomp']++;

                if (!$completo && $cantidadFotos > 0) {
                    $resumen[$id]['detalles_gnv'][] = [
                        'placa' => $exp->placa,
                        'certificado' => $exp->certificado,
                    ];
                }
            }

            // GLP
            if (in_array($tipoServicioId, $this->tiposGLP)) {
                if ($cantidadFotos === 0) {
                    $resumen[$id]['glp_sin_fotos']++;
                    $resumen[$id]['detalles_glp_sin_fotos'][] = [
                        'placa' => $exp->placa,
                        'certificado' => $exp->certificado,
                    ];
                }

                $completo
                    ? $resumen[$id]['glp_comp']++
                    : $resumen[$id]['glp_incomp']++;

                if (!$completo && $cantidadFotos > 0) {
                    $resumen[$id]['detalles_glp'][] = [
                        'placa' => $exp->placa,
                        'certificado' => $exp->certificado,
                    ];
                }
            }
        }

        foreach ($resumen as &$r) {
            $r['gnv_tot'] = $r['gnv_comp'] + $r['gnv_incomp'];
            $r['glp_tot'] = $r['glp_comp'] + $r['glp_incomp'];

            $r['gnv_pct'] = $r['gnv_tot'] > 0 ? round(($r['gnv_comp'] / $r['gnv_tot']) * 100, 1) : 0;
            $r['glp_pct'] = $r['glp_tot'] > 0 ? round(($r['glp_comp'] / $r['glp_tot']) * 100, 1) : 0;
        }

        return collect($resumen)->sortBy('inspector')->values();
    }

    /*public function verDetalles($inspectorNombre)
    {
        $resumen = $this->generarResumen();

        // Buscar por nombre directamente
        $fila = $resumen->firstWhere('inspector', $inspectorNombre);

        if (!$fila) {
            $this->detalles = ['gnv' => [], 'glp' => []];
        } else {

            $this->detalles = [
                'inspector' => $inspectorNombre,
                'gnv' => $fila['detalles_gnv'],
                'gnv_sin_fotos' => $fila['detalles_gnv_sin_fotos'] ?? [],

                'glp' => $fila['detalles_glp'],
                'glp_sin_fotos' => $fila['detalles_glp_sin_fotos'] ?? [],
            ];

        }

        $this->openModal = true;
    }*/
    
    public function verDetalles($inspectorNombre)
    {
        $fila = $this->generarResumen()
            ->firstWhere('inspector', $inspectorNombre);

        if (!$fila) return;

        $this->detalles = [
            'inspector' => $inspectorNombre,
            'gnv' => $fila['detalles_gnv'],
            'gnv_sin_fotos' => $fila['detalles_gnv_sin_fotos'],
            'glp' => $fila['detalles_glp'],
            'glp_sin_fotos' => $fila['detalles_glp_sin_fotos'],
        ];

        $this->openModal = true;
    }

    public function render()
    {
        $mostrarTabla = $this->fecIni && $this->fecFin;

        return view('livewire.reportes.reporte-fotos-por-inspector', [
            'resumen' => $mostrarTabla ? $this->generarResumen() : collect(),
            'mostrarTabla' => $mostrarTabla,
        ]);
    }

    public function exportarExcel()
    {
        //$nombreArchivo = 'reporte_fotos_' . now()->format('Ymd_His') . '.xlsx';
        $nombreArchivo = 'reporte_fotos_por_inspector_' . $this->fecIni . ' al ' . $this->fecFin . '.xlsx';
        return Excel::download(new ReporteFotosExport($this->ins, $this->fecIni, $this->fecFin, $this->estado), $nombreArchivo);
    }
}

