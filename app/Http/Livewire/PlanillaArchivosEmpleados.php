<?php

namespace App\Http\Livewire;

use App\Models\ArchivoPago;
use App\Models\Gratificacion;
use App\Models\PlanillaDetalle;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithFileUploads;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Str;

class PlanillaArchivosEmpleados extends Component
{
    use WithFileUploads;

    public $archivos;
    public $periodos = [];
    public $periodoSeleccionado;
    public $sinArchivos = false;

    // Modal boleta firmada
    public $mostrarModalBoletaFirmada = false;
    public $archivoSeleccionadoId = null;
    public $archivoSeleccionado = null;

    // Archivos a subir
    public $files = [];

    public $acepto = false;

    public function mount()
    {
        $this->cargarPeriodos();
        $this->periodoSeleccionado = $this->periodos[0] ?? null;
        $this->cargarArchivos();
    }

    public function cargarPeriodos()
    {
        $userId = auth()->id();

        // Periodos de planilla (solo user_id)
        $planillas = PlanillaDetalle::where('user_id', $userId)
            ->get()
            ->map(fn($p) => Carbon::parse($p->periodo)->format('Y-m'))
            ->toArray();

        // Periodos de gratificación
        $gratificaciones = Gratificacion::whereHas(
            'usuario',
            fn($u) => $u->where('users.id', $userId)
        )
            ->get()
            ->map(fn($g) => sprintf('%04d-%02d', $g->periodo_anio, $g->periodo_mes))
            ->toArray();

        $this->periodos = collect(array_merge($planillas, $gratificaciones))
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();
    }

    public function cargarArchivos()
    {
        if (!$this->periodoSeleccionado) {
            $this->archivos = collect();
            $this->sinArchivos = true;
            return;
        }

        [$anio, $mes] = explode('-', $this->periodoSeleccionado);
        $userId = auth()->id();

        $this->archivos = ArchivoPago::with('archivoable')
            ->where(function ($query) use ($userId, $anio, $mes) {

                // 📄 PLANILLAS (quincena + fin de mes)
                $query->whereHasMorph(
                    'archivoable',
                    PlanillaDetalle::class,
                    function ($q) use ($userId, $anio, $mes) {
                        $q->where('user_id', $userId)
                            ->whereYear('periodo', $anio)
                            ->whereMonth('periodo', $mes);
                    }
                );

                // 🎁 GRATIFICACIONES
                $query->orWhereHasMorph(
                    'archivoable',
                    Gratificacion::class,
                    function ($q) use ($userId, $anio, $mes) {
                        $q->where('periodo_anio', $anio)
                            ->where('periodo_mes', $mes)
                            ->whereHas(
                                'usuario',
                                fn($u) => $u->where('users.id', $userId)
                            );
                    }
                );
            })
            ->orderBy('archivos_pagos.created_at', 'desc')
            ->get();

        $this->sinArchivos = $this->archivos->isEmpty();
    }

    public function updatedPeriodoSeleccionado()
    {
        $this->cargarArchivos();
    }

    public function render()
    {
        return view('livewire.planilla-archivos-empleados');
    }

    public function abrirModalBoletaFirmada($archivoId)
    {
        $this->archivoSeleccionadoId = $archivoId;
        $this->archivoSeleccionado = ArchivoPago::with('archivoable')->findOrFail($archivoId);

        //$this->files = [];
        $this->acepto = false; // Resetear el checkbox al abrir
        $this->mostrarModalBoletaFirmada = true;
    }
    /*public function guardarBoletaFirmada()
    {
        $this->validate([
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if (!$this->archivoSeleccionado) {
            return;
        }
        foreach ($this->files as $file) {

            $original = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $path = $file->store('planillas', 'public');

            ArchivoPago::create([
                'archivoable_id'   => $this->archivoSeleccionado->archivoable_id,
                'archivoable_type' => $this->archivoSeleccionado->archivoable_type,
                'tipo'             => $this->archivoSeleccionado->tipo,
                'estado'           => 'firmado',
                'nombre'           => $original,
                'ruta'             => $path,
                'extension'        => $ext,
            ]);
        }

        $this->mostrarModalBoletaFirmada = false;
        $this->files = [];

        $this->cargarArchivos();
        $this->emit("minAlert", ["titulo" => "BUEN TRABAJO!", "mensaje" => "Boleta firmada subida correctamente.", "icono" => "success",]);
    }*/
    public function guardarBoletaFirmada()
    {
        $this->validate(['acepto' => 'accepted'], ['acepto.accepted' => 'Debe aceptar la conformidad.']);
        if (!$this->archivoSeleccionado) return;

        $user = auth()->user();

        try {
            // 1. Generamos el nombre físico igual que lo haría Laravel: planillas/HASH.pdf
            // md5(uniqid()) genera esa cadena de texto "indescifrable" sin importar nada
            $nombreFisico = 'FIRMADO_' . time() . '_' . md5(uniqid()) . '.pdf';
            $nuevaRutaRelativa = 'planillas/' . $nombreFisico;

            $rutaOriginal = storage_path('app/public/' . $this->archivoSeleccionado->ruta);
            $rutaDestino = storage_path('app/public/' . $nuevaRutaRelativa);

            // 2. Proceso de estampado (FPDI)
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($rutaOriginal);
            for ($n = 1; $n <= $pageCount; $n++) {
                $tplIdx = $pdf->importPage($n);
                $pdf->addPage();
                $pdf->useTemplate($tplIdx, 0, 0, 210);

                if ($n === 1 && $user->rutaFirma) {
                    $rutaFirma = storage_path('app/public/' . str_replace('public/', '', $user->rutaFirma));
                    if (file_exists($rutaFirma)) {
                        /**
                         * Parámetros de Image: (ruta, x, y, ancho, alto)
                         * Subimos el ancho a 55 y el alto a 25 para que sea más grande.
                         * Bajamos la posición Y a 230 para que esté más cerca del pie de página.
                         */
                        $pdf->Image($rutaFirma, 25, 230, 65, 30);
                        $pdf->SetFont('Arial', '', 7);
                        // Colocamos el texto a ras de la firma para no pasarnos del borde de la hoja
                        $pdf->SetXY(25, 260); 
                        $pdf->Cell(65, 5, 'Firmado digitalmente por: ' . $user->name, 0, 0, 'C');
                    }
                }
            }

            // 3. Guardar el archivo físicamente
            $pdf->Output($rutaDestino, 'F');

            // 4. Guardar en BD
            ArchivoPago::create([
                'archivoable_id'   => $this->archivoSeleccionado->archivoable_id,
                'archivoable_type' => $this->archivoSeleccionado->archivoable_type,
                'tipo'             => $this->archivoSeleccionado->tipo,
                'estado'           => 'firmado',
                'nombre'           => 'FIRMADO_' . $this->archivoSeleccionado->nombre,
                'ruta'             => $nuevaRutaRelativa,
                'extension'        => 'pdf',
            ]);

            $this->reset(['mostrarModalBoletaFirmada', 'acepto']);
            $this->cargarArchivos();
            $this->emit("minAlert", ["titulo" => "¡EXITO!", "mensaje" => "Tu boleta ha sido firmada digitalmente.", "icono" => "success"]);
        } catch (\Exception $e) {
            $this->emit("minAlert", ["titulo" => "ERROR", "mensaje" => $e->getMessage(), "icono" => "error"]);
        }
    }
}

/*  public $periodoSeleccionado;
    public $detalles;
    public function mount()
    {
        $userId = auth()->id();

        // Buscar el último periodo de planilla para este usuario (contrato o apoyo eventual)
        $this->periodoSeleccionado = PlanillaDetalle::where(function ($query) use ($userId) {
            $query->where('user_id', $userId) // apoyo eventual
                ->orWhereHas('contrato', function ($q) use ($userId) {
                    $q->where('idUser', $userId); // empleados con contrato
                });
        })
            ->max('periodo');
    }
    public function render()
    {
        $userId = auth()->id();

        // Todos los periodos disponibles para este usuario
        $periodos = PlanillaDetalle::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhereHas('contrato', function ($q) use ($userId) {
                    $q->where('idUser', $userId);
                });
        })
            ->distinct()
            ->orderBy('periodo', 'desc')
            ->pluck('periodo');

        // Si no hay ningún periodo, el usuario nunca tendrá planillas
        if ($periodos->isEmpty()) {
            $this->detalles = collect(); // vacío
            return view('livewire.planilla-archivos-empleados', [
                'periodos' => $periodos,
                'sinPlanilla' => true
            ]);
        }

        // Si hay periodos pero aún no se ha seleccionado, ponemos el más reciente
        if (!$this->periodoSeleccionado) {
            $this->periodoSeleccionado = $periodos->first();
        }

        // Cargar detalles del periodo actual
        $this->detalles = PlanillaDetalle::with('archivos')
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('contrato', function ($q) use ($userId) {
                        $q->where('idUser', $userId);
                    });
            })
            ->where('periodo', $this->periodoSeleccionado)
            ->get();

        return view('livewire.planilla-archivos-empleados', [
            'periodos' => $periodos,
            'sinPlanilla' => false
        ]);
    }
*/