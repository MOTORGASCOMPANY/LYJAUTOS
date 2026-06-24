<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastoAdministrativoPersonal extends Model
{
    use HasFactory;

    protected $table = 'gastos_administrativos_personal';

    protected $fillable = [
        'gasto_administrativo_id',
        'user_id',
        'sueldo',
        'cts',
        'gratificacion',
        'essalud',
        'planilla',
        'vacacion',
        'otros',
        'total',
    ];

    protected $casts = [
        'sueldo'        => 'decimal:2',
        'cts'           => 'decimal:2',
        'gratificacion' => 'decimal:2',
        'essalud'       => 'decimal:2',
        'planilla'       => 'decimal:2',
        'vacacion'      => 'decimal:2',
        'otros'         => 'decimal:2',
        'total'         => 'decimal:2',
    ];

    //Relaciones
    public function gastoAdministrativo()
    {
        return $this->belongsTo(GastoAdministrativo::class, 'gasto_administrativo_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Helpers
    public function calcularTotal()
    {
        $this->total = $this->sueldo + $this->cts + $this->gratificacion + $this->essalud + $this->planilla + $this->vacacion + $this->otros;
        $this->save();

        // Actualizar total del gasto administrativo
        $this->gastoAdministrativo?->recalcularTotal();
    }

    /*protected static function booted()
    {
        static::saved(function ($personal) {
            $personal->gastoAdministrativo?->recalcularTotal();
        });

        static::deleted(function ($personal) {
            $personal->gastoAdministrativo?->recalcularTotal();
        });
    }*/

}
