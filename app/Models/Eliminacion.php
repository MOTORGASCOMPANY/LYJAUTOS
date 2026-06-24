<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eliminacion extends Model
{
    use HasFactory;
    
    protected $table = 'eliminacion';

    public $fillable=[
        "id",
        "placa",
        "numSerie", // AHORA representará el número de serie del CERTIFICADO
        "numSerieMaterial", // NUEVO: La serie del material/formato que se echó a perder o se eliminó
        "tipoServicio",
        "anioSerie",      // NUEVO: El año de la serie del certificado (Esencial para la búsqueda)
        "grupoServicio",  // NUEVO: Para saber si la serie era de GNV, GLP o MOD
        "estado",         // NUEVO: 1 = Pendiente, 2 = Aprobado (Disponible para reusar), 3 = Ya reutilizado
    ];
}
