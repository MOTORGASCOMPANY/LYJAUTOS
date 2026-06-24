<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDocumentoEmpleado extends Model
{
    use HasFactory;

    protected $table="tipodocumentoempleado";

    public $fillable = [
        'nombreTipo', // 1=dni, 2=antecendepenal, 3=antecendejudicial, 4=contratofirmado, 5=acreditacion
    ];

    public function documentos()
    {
        return $this->hasMany(DocumentoEmpleado::class, 'tipoDocumento');
    }

}
