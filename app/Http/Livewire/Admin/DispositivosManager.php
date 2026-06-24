<?php

namespace App\Http\Livewire\Admin;

use App\Models\DispositivoAutorizado;
use Livewire\Component;
use Illuminate\Support\Str;

class DispositivosManager extends Component
{
    public $nombre_estacion, $descripcion_ubicacion;
    public $search = '';

    protected $rules = [
        'nombre_estacion' => 'required|min:3',
        'descripcion_ubicacion' => 'required',
    ];

    // Función principal para autorizar la laptop donde estás sentado
    public function autorizarEstaEstacion()
    {
        $this->validate();

        $userAgent = request()->header('User-Agent');

        // VALIDACIÓN: Solo PC/Laptop
        $moviles = ['Mobile', 'Android', 'iPhone', 'iPad', 'Windows Phone'];
        foreach ($moviles as $movil) {
            if (stripos($userAgent, $movil) !== false) {
                $this->emit("minAlert", ["titulo" => "ERROR", "mensaje" => "No puedes autorizar un celular. Solo PCs o Laptops.", "icono" => "error"]);
                return;
            }
        }

        // Generar Token Único
        $token = 'MTG_' . Str::random(60);

        // Guardar en BD
        DispositivoAutorizado::create([
            'device_token' => $token,
            'nombre_estacion' => $this->nombre_estacion,
            'descripcion_ubicacion' => $this->descripcion_ubicacion,
            'sistema_operativo' => $this->getSO($userAgent),
            'navegador' => $this->getBrowser($userAgent),
            'ultima_ip' => request()->ip(),
            'ultima_conexion' => now(),
            'esta_activo' => true
        ]);

        // Enviar el token al navegador para que se guarde en LocalStorage
        $this->dispatchBrowserEvent('save-device-token', ['token' => $token]);
        
        $this->reset(['nombre_estacion', 'descripcion_ubicacion']);
    }

    public function desactivar($id)
    {
        DispositivoAutorizado::find($id)->update(['esta_activo' => false]);
    }

    private function getSO($ua) {
        if (stripos($ua, 'windows')) return 'Windows';
        if (stripos($ua, 'macintosh')) return 'macOS';
        if (stripos($ua, 'linux')) return 'Linux';
        return 'Desconocido';
    }

    private function getBrowser($ua) {
        if (stripos($ua, 'edg')) return 'Edge';
        if (stripos($ua, 'chrome')) return 'Chrome';
        if (stripos($ua, 'firefox')) return 'Firefox';
        return 'Otro';
    }

    public function render()
    {
        return view('livewire.admin.dispositivos-manager', [
            'dispositivos' => DispositivoAutorizado::where('nombre_estacion', 'like', '%'.$this->search.'%')->get()
        ]);
    }
    
}
