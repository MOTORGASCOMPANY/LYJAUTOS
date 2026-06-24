<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Anulacion extends Model
{
    use HasFactory;

    protected $table = 'anulacion';

    protected $fillable =
    [
        'motivo',
        'idCertificacion',
        'idMaterial', // NUEVO: Para guardar el ID del material a sustituir
        'idNuevoMaterial', // NUEVO: Para guardar el ID del material de reemplazo
        'estado', // estado: 1 = Pendiente, 2 = Aprobado, 3 = Rechazado

        // Separar logica (nueva tabla) de devolcuion formatos malogrados de anulacion 
        'idUsuario',
        'idTipoMaterial',
        'anioActivo',
        'cart_id',
        'numSeries',
        
    ];

    public function materialSustituir()
    {
        return $this->belongsTo(Material::class, 'idMaterial');
    }
    public function nuevoMaterial()
    {
        return $this->belongsTo(Material::class, 'idNuevoMaterial');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuario');
    }
    public function material()
    {
        return $this->belongsTo(TipoMaterial::class, 'idTipoMaterial');
    }
    public function scopeIdInspector(Builder $query, string $search): void
    {
        if ($search) {
            $query->where('idUsuario', $search);
        }
    }
    public function scopeRangoFecha(Builder $query, string $desde, string $hasta): void
    {
        if ($desde && $hasta) {
            $query->whereBetween('created_at', ["$desde 00:00:00", "$hasta 23:59:59"]);
        }
    }
    public function getRutaVistaCertificadoAttribute()
    {
        $ruta = null;
        $ruta = route('SolicitudDevolucion', ['cart_id' => $this->attributes['cart_id']]);
        return $ruta;
    }
}
