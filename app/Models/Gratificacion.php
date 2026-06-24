<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gratificacion extends Model
{
    use HasFactory;

    protected $table = 'gratificaciones';

    protected $fillable = [
        'contrato_id',
        'periodo_mes',
        'periodo_anio',
        'taller',
        'fecha_inicio',
        'meses_completos',
        'sueldo',
        'asignacion',
        'monto',
        'bonificacion',
        'monto_final',
        'estado',
        'observacion',
        'numero_cuenta',
        'pagado',
        'fecha_pago',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_pago'   => 'datetime',
        'periodo_anio' => 'integer',
        'periodo_mes'  => 'integer',
        'meses_completos' => 'integer',
        'pagado' => 'boolean',
    ];

    // Relación con contrato_trabajo
    public function contrato()
    {
        return $this->belongsTo(ContratoTrabajo::class, 'contrato_id');
    }

    public function archivosPagos()
    {
        return $this->morphMany(ArchivoPago::class, 'archivoable');
    }


    // Relación indirecta hacia users (empleado)
    public function usuario()
    {
        // A través del contrato
        return $this->hasOneThrough(
            User::class,
            ContratoTrabajo::class,
            'id',       // PK en contrato_trabajo
            'id',       // PK en users
            'contrato_id', // FK en gratificaciones
            'idUser'       // FK en contrato_trabajo
        );
    }
}
