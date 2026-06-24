<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioDetalle extends Model
{
    use HasFactory;

    protected $table = 'horario_detalles';

    protected $fillable = [
        'horario_id',
        'dia_semana',
        'es_laborable', 
        'hora_entrada',
        'hora_salida',
        'tolerancia_tardanza',
        'hora_descanso_inicio',
        'hora_descanso_fin'
    ];

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }
}
