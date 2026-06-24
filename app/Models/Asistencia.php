<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    //public $timestamps = false;

    protected $fillable = [
        'user_id', 'fecha', 'hora_entrada', 'hora_salida', 
        'minutos_trabajados', 'minutos_tardanza', 'horas_extras_minutos', 
        'estado', 'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_entrada' => 'datetime',
        'hora_salida' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
