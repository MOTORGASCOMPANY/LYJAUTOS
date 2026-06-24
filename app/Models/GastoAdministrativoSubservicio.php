<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastoAdministrativoSubservicio extends Model
{
    use HasFactory;

    protected $table = 'gastos_administrativos_subservicios';

    protected $fillable = [
        'gasto_servicio_id',
        'monto',
        'fecha',
        'descripcion',
        'pagado',
        'fecha_pago',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'pagado' => 'boolean',
        'fecha_pago' => 'datetime',
    ];

    // relaciones
    public function servicio()
    {
        return $this->belongsTo(GastoAdministrativoServicio::class, 'gasto_servicio_id');
    }

    // Events
    protected static function booted()
    {
        static::saved(function ($subservicio) {
            $subservicio->servicio?->recalcularDesdeSubservicios();
        });

        static::deleted(function ($subservicio) {
            $subservicio->servicio?->recalcularDesdeSubservicios();
        });
    }
}
