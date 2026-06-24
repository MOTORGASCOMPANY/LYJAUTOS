<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispositivoAutorizado extends Model
{
    use HasFactory;

    protected $table = 'dispositivos_autorizados';

    protected $fillable = [
        'device_token', 
        'nombre_estacion', 
        'descripcion_ubicacion',
        'navegador', 
        'sistema_operativo', 
        'ultima_ip', 
        'ultima_conexion', 
        'esta_activo'
    ];
}
