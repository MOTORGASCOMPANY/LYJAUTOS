<?php

namespace App\Http\Livewire;

use App\Models\Horario;
use App\Models\User;
use App\Models\UsuarioHorario;
use Livewire\Component;
use Livewire\WithPagination;

class AsignarHorario extends Component
{
    use WithPagination;

    // Propiedades para el formulario
    public $user_id, $horario_id, $fecha_inicio, $fecha_fin;
    public $search = '';

    // Colecciones que cargaremos al inicio
    public $usuarios = [];
    public $horarios = [];

    protected $rules = [
        'user_id' => 'required|exists:users,id',
        'horario_id' => 'required|exists:horarios,id',
        'fecha_inicio' => 'required|date',
    ];

    public function mount()
    {
        $this->usuarios = User::orderBy('name', 'asc')->get(['id', 'name', 'dni']);
        $this->horarios = Horario::where('activo', true)->get(['id', 'nombre']);
        $this->fecha_inicio = now()->format('Y-m-d'); // Fecha actual por defecto
    }

    public function guardarAsignacion()
    {
        $this->validate();

        try {
            // Desactivar horarios anteriores
            UsuarioHorario::where('user_id', $this->user_id)
                ->where('activo', true)
                ->update([
                    'activo' => false,
                    'fecha_fin' => now()->subDay()->format('Y-m-d')
                ]);

            // Crear nueva asignación
            UsuarioHorario::create([
                'user_id' => $this->user_id,
                'horario_id' => $this->horario_id,
                'fecha_inicio' => $this->fecha_inicio,
                'activo' => true,
            ]);

            $this->reset(['user_id', 'horario_id']);
            $this->emit("minAlert", ["titulo" => "¡ASIGNACIÓN EXITOSA!", "mensaje" => "El horario ha sido vinculado correctamente.", "icono" => "success"]);
            
        } catch (\Exception $e) {
            $this->emit("minAlert", ["titulo" => "ERROR", "mensaje" => "No se pudo procesar la asignación.", "icono" => "error"]);
        }
    }

    public function render()
    {
        // La tabla de asignados sí debe estar en el render para refrescarse al guardar
        return view('livewire.asignar-horario', [
            'horariosAsignados' => UsuarioHorario::with(['usuario', 'horario'])
                ->where('activo', true)
                ->latest()
                ->get()
        ]);
    }
}
