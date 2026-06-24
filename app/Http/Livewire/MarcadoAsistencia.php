<?php

namespace App\Http\Livewire;

use App\Models\Asistencia;
use App\Models\DispositivoAutorizado;
use App\Models\HorarioDetalle;
use App\Models\MarcacionRaw;
use App\Models\User;
use App\Models\UsuarioHorario;
use Carbon\Carbon;
use Livewire\Component;

class MarcadoAsistencia extends Component
{
    public $dni;
    public $tipo = 'Entrada'; // Se actualizará dinámicamente o servirá de fallback

    // propiedades para verificar dispositivo autorizado
    public $deviceToken; 
    public $isDeviceAuthorized = false;

    // método para verificar el token desde el navegador
    public function checkDevice($browserToken)
    {
        $this->deviceToken = $browserToken;
        $dispositivo = DispositivoAutorizado::where('device_token', $browserToken)
                        ->where('esta_activo', true)
                        ->first();

        if ($dispositivo) {
            $this->isDeviceAuthorized = true;
            $dispositivo->update([
                'ultima_ip' => request()->ip(),
                'ultima_conexion' => now()
            ]);
        }
    }

    public function registrarMarcado()
    {
        // --- CANDADO DE SEGURIDAD EN EL SERVIDOR ---
        if (!$this->isDeviceAuthorized) {
            $this->emit("minAlert", ["titulo" => "ERROR DE SEGURIDAD", "mensaje" => "Intento de marcado desde dispositivo no autorizado", "icono" => "error"]);
            return;
        }

        $this->validate([
            'dni' => 'required|digits:8',
            //'tipo' => 'required|in:Entrada,Salida'
        ]);

        // OPCIONAL: log de MarcacionRaw, registra el ID de la laptop
        $dispositivo = DispositivoAutorizado::where('device_token', $this->deviceToken)->first();

        $usuario = User::where('dni', $this->dni)->first();

        if (!$usuario) {
            $this->emit("minAlert", ["titulo" => "DNI NO ENCONTRADO", "mensaje" => "Consulte con administración", "icono" => "error"]);
            $this->reset('dni');
            return;
        }

        // 2. FILTRO: ¿Es personal de planilla / Tiene horario asignado?
        $horarioAsignado = UsuarioHorario::where('user_id', $usuario->id)
                            ->where('activo', true)
                            ->first();

        if (!$horarioAsignado) {
            $this->emit("minAlert", ["titulo" => "ACCESO DENEGADO", "mensaje" => "Usuario no habilitado para control de asistencia", "icono" => "warning"]);
            $this->reset('dni');
            return;
        }

        $ahora = Carbon::now();
        $fechaHoy = $ahora->format('Y-m-d');

        // --- LÓGICA AUTOMÁTICA DE DETECCIÓN ---
        // Buscamos si el usuario ya tiene un registro de asistencia el día de hoy
        $asistenciaHoy = Asistencia::where('user_id', $usuario->id)
                                    ->where('fecha', $fechaHoy)
                                    ->first();

        // Si ya tiene entrada marcada (hora_entrada no es nula), toca registrar Salida.
        // De lo contrario, es una Entrada.
        if ($asistenciaHoy && !is_null($asistenciaHoy->hora_entrada)) {
            $this->tipo = 'Salida';
        } else {
            $this->tipo = 'Entrada';
        }

        // 1. Registro Log (Auditoría)
        MarcacionRaw::create([
            'user_id' => $usuario->id,
            'dni_usado' => $this->dni,
            'momento_marcado' => $ahora,
            'tipo' => $this->tipo,
            'ip_origen' => request()->ip(),
            'metodo_verificacion' => 'PC: ' . ($dispositivo->nombre_estacion ?? 'DESCONOCIDA')
        ]);

        // 2. Procesar Lógica según selección manual
        if ($this->tipo == 'Entrada') {
            $this->procesarEntrada($usuario, $ahora, $fechaHoy, $horarioAsignado);
        } else {
            $this->procesarSalida($usuario, $ahora, $fechaHoy, $asistenciaHoy);
        }

        $this->reset('dni');
    }

    private function procesarEntrada($usuario, $ahora, $fechaHoy, $horarioAsignado) {
        $minutosTardanza = 0;
        $estado = 'Puntual';

        // Ya no buscamos $horarioAsignado aquí, lo recibimos por parámetro
        $diaSemana = $ahora->dayOfWeekIso;
        $detalle = HorarioDetalle::where('horario_id', $horarioAsignado->horario_id)->where('dia_semana', $diaSemana)->first();

        if ($detalle && $detalle->es_laborable) {
            $entradaProg = Carbon::createFromFormat('H:i:s', $detalle->hora_entrada);
            $entradaReal = Carbon::createFromFormat('H:i:s', $ahora->format('H:i:s'));
            
            // Comparamos solo horas para evitar problemas de fechas
            $diff = $entradaProg->diffInMinutes($entradaReal, false);
            
            if ($diff > $detalle->tolerancia_tardanza) {
                $minutosTardanza = $diff;
                $estado = 'Tardanza';
            }
        }

        Asistencia::updateOrCreate(
            ['user_id' => $usuario->id, 'fecha' => $fechaHoy],
            ['hora_entrada' => $ahora, 'minutos_tardanza' => $minutosTardanza, 'estado' => $estado]
        );

        $this->emit("minAlert", ["titulo" => "¡HOLA, ".strtoupper($usuario->name)."!", "mensaje" => "ENTRADA REGISTRADA", "icono" => "success"]);
    }

    private function procesarSalida($usuario, $ahora, $fechaHoy, $asistencia) {
        //$asistencia = Asistencia::where('user_id', $usuario->id)->where('fecha', $fechaHoy)->first();
        
        if ($asistencia) {
            $entrada = Carbon::parse($asistencia->hora_entrada);
            $asistencia->update([
                'hora_salida' => $ahora,
                'minutos_trabajados' => $entrada->diffInMinutes($ahora)
            ]);
        } else {
            // Si marcó salida sin haber marcado entrada
            Asistencia::create([
                'user_id' => $usuario->id,
                'fecha' => $fechaHoy,
                'hora_salida' => $ahora,
                'estado' => 'Incompleto'
            ]);
        }
        //$this->emit("minAlert", ["titulo" => "¡ADIÓS!", "mensaje" => "SALIDA REGISTRADA", "icono" => "info"]);
        $this->emit("minAlert", ["titulo" => "¡ADIÓS, ".strtoupper($usuario->name)."!", "mensaje" => "SALIDA REGISTRADA", "icono" => "info"]);
    }
    
    public function render() {
        return view('livewire.marcado-asistencia')->layout('layouts.guest');
    }
}
