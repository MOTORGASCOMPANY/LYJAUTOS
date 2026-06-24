<?php

namespace App\Http\Livewire\Admin;

use App\Models\Horario;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class HorariosManager extends Component
{
    use WithPagination;

    public $horarioId, $nombre, $descripcion, $activo = true;

    public $detalles = [];

    public $isModalOpen = false;
    public $search = '';

    protected $nombresDias = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo'
    ];

    protected $rules = [
        'nombre' => 'required|min:3',
        'detalles.*.hora_entrada' => 'required_if:detalles.*.es_laborable,true',
        'detalles.*.hora_salida' => 'required_if:detalles.*.es_laborable,true',
    ];

    public function mount()
    {
        $this->resetDetalles();
    }

    public function resetDetalles()
    {
        $this->detalles = [];
        foreach ($this->nombresDias as $numero => $nombre) {
            $this->detalles[] = [
                'dia_semana' => $numero,
                'nombre_dia' => $nombre,
                'es_laborable' => true,
                'hora_entrada' => '08:30',
                'hora_salida' => '17:30',
                'tolerancia_tardanza' => 10,
                'hora_descanso_inicio' => '13:00',
                'hora_descanso_fin' => '14:00',
            ];
        }
    }

    public function create()
    {
        $this->resetInputFields();
        $this->resetDetalles();
        $this->isModalOpen = true;
    }

    public function edit($id)
    {
        $horario = Horario::with('detalles')->findOrFail($id);
        $this->horarioId = $id;
        $this->nombre = $horario->nombre;
        $this->descripcion = $horario->descripcion;
        $this->activo = $horario->activo;

        //$this->detalles = $horario->detalles->toArray();
        // Al cargar de la BD, necesitamos re-inyectar el 'nombre_dia' para la vista
        $this->detalles = $horario->detalles->map(function ($item) {
            $data = $item->toArray();
            $data['nombre_dia'] = $this->nombresDias[$item->dia_semana] ?? 'Desconocido';
            return $data;
        })->toArray();
        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $horario = Horario::updateOrCreate(['id' => $this->horarioId], [
                    'nombre' => $this->nombre,
                    'descripcion' => $this->descripcion,
                    'activo' => $this->activo,
                ]);

                $horario->detalles()->delete();

                foreach ($this->detalles as $detalle) {
                    $dataParaInsertar = $detalle;
                    unset($dataParaInsertar['nombre_dia']);

                    // 1. Limpieza si no es laborable
                    if (!$dataParaInsertar['es_laborable']) {
                        $dataParaInsertar['hora_entrada'] = null;
                        $dataParaInsertar['hora_salida'] = null;
                        $dataParaInsertar['hora_descanso_inicio'] = null;
                        $dataParaInsertar['hora_descanso_fin'] = null;
                    } else {
                        // 2. TRUCO: Convertir cadenas vacías en NULL para evitar error SQL 22007
                        // Esto permite que si borras el descanso del sábado, se guarde como null
                        $dataParaInsertar['hora_entrada'] = $dataParaInsertar['hora_entrada'] ?: null;
                        $dataParaInsertar['hora_salida'] = $dataParaInsertar['hora_salida'] ?: null;
                        $dataParaInsertar['hora_descanso_inicio'] = $dataParaInsertar['hora_descanso_inicio'] ?: null;
                        $dataParaInsertar['hora_descanso_fin'] = $dataParaInsertar['hora_descanso_fin'] ?: null;
                    }

                    $horario->detalles()->create($dataParaInsertar);
                }
            });

            $accion = $this->horarioId ? "actualizado" : "creado";
            $this->emit("minAlert", ["titulo" => "¡BUEN TRABAJO!", "mensaje" => "El horario se ha {$accion} correctamente.", "icono" => "success"]);
            $this->reset(["isModalOpen"]);
            $this->resetPage();
        } catch (\Exception $e) {
            $this->emit("minAlert", ["titulo" => "ERROR", "mensaje" => "No se pudo guardar: " . $e->getMessage(), "icono" => "error"]);
        }
    }

    private function resetInputFields()
    {
        $this->horarioId = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->activo = true;
    }

    public function render()
    {
        return view('livewire.admin.horarios-manager', [
            'horarios' => Horario::where('nombre', 'like', '%' . $this->search . '%')->paginate(10)
        ]);
    }
}
