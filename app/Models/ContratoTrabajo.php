<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratoTrabajo extends Model
{
    use HasFactory;
    protected $table = 'contrato_trabajo';

    /**
     * El campo fechaInicio es la fecha en la cual empieza a trabajar en la empresa , fechaIniciodos puede ser la misma que fechaInicio claro que si pero en caso de una posible
     * renovacion fechaInicio se mantiene y solo se actualiza fechaIniciodos y fechaExpiracion
     * 
     */
    
    protected $fillable = [
        'idUser',
        'fechaInicio',
        'fechaIniciodos',
        'fechaExpiracion',
        'cargo',
        'pago',
        'sueldo_neto',        
        'cont_externo',
    ];

    // Relación con el usuario empleado
    public function empleado()
    {
        return $this->belongsTo(User::class, 'idUser');
    }

    public function Documentos()
    {
        return $this->belongsToMany(DocumentoEmpleado::class, 'documentoempleado_user', 'idUser', 'idDocumentoEmpleado');
    }

    public function vacaciones()
    {
        return $this->hasOne(Vacacion::class, 'idContrato');
    }

    
    public function planillas()
    {
        return $this->hasMany(PlanillaDetalle::class, 'contrato_id');
    }

    public function gratificaciones()
    {
        // Un contrato tiene muchas gratificaciones
        return $this->hasMany(Gratificacion::class, 'contrato_id');
    }

    public function getRutaVistaContratoTrabajoAttribute()
    {
        $ruta = route('contratoTrabajo', ['id' => $this->attributes['id']]);
        return $ruta;
    }

    public function getRutaDescargaContratoTrabajoAttribute()
    {
        $ruta = $ruta = route('descargarContratoTrabajo', ['id' => $this->attributes['id']]);
        return $ruta;
    }
}
