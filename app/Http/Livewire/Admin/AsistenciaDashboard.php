<?php

namespace App\Http\Livewire\Admin;

use App\Models\Asistencia;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class AsistenciaDashboard extends Component
{
    use WithPagination;

    public $search = '';
    public $filtroEstado = ''; // Puntual, Tardanza, Incompleto

    public $mostrarModalAusentes = false;

    protected $listeners = ['echo:asistencia,AsistenciaMarcada' => '$refresh'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $hoy = Carbon::today()->toDateString();
        // Carbon devuelve de 1 (Lunes) a 7 (Domingo) con dayOfWeekIso
        $diaSemanaActual = Carbon::today()->dayOfWeekIso;

        // 1. Tabla de Asistencias (Principal)
        $asistencias = Asistencia::where('fecha', $hoy)
            ->whereHas('usuario', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('dni', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            // Cargamos la asignación activa, su horario y únicamente el detalle del día de hoy
            ->with(['usuario.horariosAsignados' => function($q) use ($diaSemanaActual) {
                $q->where('activo', true)->with(['horario.detalles' => function($queryDetalle) use ($diaSemanaActual) {
                    $queryDetalle->where('dia_semana', $diaSemanaActual);
                }]);
            }])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        // 2. Estadísticas Globales
        $statsRaw = Asistencia::where('fecha', $hoy)
            ->selectRaw("COUNT(*) as total, 
                         SUM(CASE WHEN estado = 'Tardanza' THEN 1 ELSE 0 END) as tardanzas,
                         SUM(CASE WHEN estado = 'Puntual' THEN 1 ELSE 0 END) as puntuales")
            ->first();

        $stats = [
            'total' => $statsRaw->total ?? 0,
            'tardanzas' => $statsRaw->tardanzas ?? 0,
            'puntuales' => $statsRaw->puntuales ?? 0,
        ];

        // 3. Ausentes y Gráfico
        $usuariosAusentesBase = User::whereHas('horariosAsignados', fn($q) => $q->where('activo', true))
            ->whereDoesntHave('asistencias', fn($q) => $q->where('fecha', $hoy));
            //->count();

        $ausentesCount = $usuariosAusentesBase->count();

            // Obtenemos los nombres, DNI e ID de los ausentes para el desglose visual
        $listaAusentes = $usuariosAusentesBase->select('id', 'name', 'dni')->get();

        $dataGrafico = [
            'asistencias' => $stats['total'],
            'ausencias' => $ausentesCount
        ];

        // 4. Actividad Reciente (Entradas y Salidas mezcladas)
        $actividadReciente = Asistencia::where('fecha', $hoy)
            ->with('usuario')
            ->orderBy('updated_at', 'desc')
            ->take(20)
            ->get();

        // 5. Asistencia por Roles (Sustituye a sectores)
        // Filtramos solo roles relevantes para no llenar la vista
        $rolesInteres = ['administrador', 'inspector', 'Administrador del sistema'];
        $asistenciaPorRol = collect();

        foreach ($rolesInteres as $roleName) {
            $totalUsuarios = User::role($roleName)->count();
            if ($totalUsuarios > 0) {
                $asistieron = Asistencia::where('fecha', $hoy)
                    ->whereHas('usuario', fn($q) => $q->role($roleName))
                    ->count();

                $porcentaje = round(($asistieron / $totalUsuarios) * 100);

                $asistenciaPorRol->push([
                    'nombre' => ucfirst($roleName),
                    'porcentaje' => $porcentaje,
                    'color' => $porcentaje < 50 ? 'bg-red-500' : ($porcentaje < 85 ? 'bg-orange-500' : 'bg-indigo-600')
                ]);
            }
        }

        $this->dispatchBrowserEvent('contentChanged');

        return view('livewire.admin.asistencia-dashboard', [
            'asistencias' => $asistencias,
            'stats' => $stats,
            'dataGrafico' => $dataGrafico,
            'actividadReciente' => $actividadReciente,
            'asistenciaPorRol' => $asistenciaPorRol,
            'listaAusentes' => $listaAusentes
        ]);
    }
}
