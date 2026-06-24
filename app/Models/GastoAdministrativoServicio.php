<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastoAdministrativoServicio extends Model
{
    use HasFactory;

    protected $table = 'gastos_administrativos_servicios';

    protected $fillable = [
        'gasto_administrativo_id',
        'concepto',
        'monto_presupuestado',
        'monto',
        'proveedor',
        'pagado', // 1 = pago realizado, 0 = pendiente
        'fecha_pago', // fecha en que se realizo el pago
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'monto_presupuestado' => 'decimal:2',
        'pagado' => 'boolean',
    ];

    // Relaicones
    public function gastoAdministrativo()
    {
        return $this->belongsTo(GastoAdministrativo::class, 'gasto_administrativo_id');
    }

    public function subservicios()
    {
        return $this->hasMany(GastoAdministrativoSubservicio::class, 'gasto_servicio_id');
    }


    public function archivosPagos()
    {
        return $this->morphMany(ArchivoPago::class, 'archivoable');
    }

    // Events (opcional pero recomendado)
    protected static function booted()
    {
        static::saved(function ($servicio) {
            $servicio->gastoAdministrativo?->recalcularTotal();
        });

        static::deleted(function ($servicio) {
            $servicio->gastoAdministrativo?->recalcularTotal();
        });
    }

    public function recalcularDesdeSubservicios()
    {
        // Suma de montos reales
        $total = $this->subservicios()->sum('monto');

        // Total de subservicios
        $totalSub = $this->subservicios()->count();

        // Total pagados
        $pagados = $this->subservicios()->where('pagado', 1)->count();

        // ¿Todo pagado?
        $pagado = $totalSub > 0 && $totalSub === $pagados;

        $this->update([
            'monto' => $total,
            'pagado' => $pagado,
            'fecha_pago' => $pagado ? now() : null,
        ]);

        // Recalcula el total general mensual
        $this->gastoAdministrativo?->recalcularTotal();
    }

}
