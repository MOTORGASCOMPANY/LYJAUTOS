<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaContabilidad extends Model
{
    use HasFactory;

    protected $table = 'facturas_contabilidad';

    protected $fillable = [
        'tipo',
        'numero',
        'proveedor',
        'ruc',
        'igv',
        'fecha_emision',
        'monto_total',
        'descripcion',
        'nombre',
        'ruta',
        'extension',
        'usuario_id',
        'estado'
    ];

    // Relación con usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
