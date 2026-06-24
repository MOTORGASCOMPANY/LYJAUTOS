<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GastoAdministrativo extends Model
{
    use HasFactory;

    protected $table = 'gastos_administrativos';

    protected $fillable = [
        'periodo_anio',
        'periodo_mes',
        'total',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'periodo_anio' => 'integer',
        'periodo_mes'  => 'integer',
        'total'        => 'decimal:2',
    ];

    // Relaciones
    public function personal()
    {
        return $this->hasMany(GastoAdministrativoPersonal::class, 'gasto_administrativo_id');
    }

    public function servicios()
    {
        return $this->hasMany(GastoAdministrativoServicio::class, 'gasto_administrativo_id');
    }

    // Helpers
    public function recalcularTotal()
    {
        $totalPersonal  = $this->personal()->sum('total');
        $totalServicios = $this->servicios()->sum('monto');

        $this->total = $totalPersonal + $totalServicios; 
        //$this->total = $totalServicios; // Solo servicios afectan el total
        $this->save();
    }
}
