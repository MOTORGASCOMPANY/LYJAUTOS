<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoServicio extends Model
{
    use HasFactory;
    protected $table = 'tiposervicio';

    public $fillable=["id","descripcion"];

    /**
     * 1 = Conversión a GNV -> (SERVICIO GNV)
     * 2 = Revisión anual GNV -> (SERVICIO GNV)
     * 3 = Conversión a GLP -> (SERVICIO GLP)
     * 4 = Revisión anual GLP -> (SERVICIO GLP)
     * 5 = Modificación -> (SERVICIO MODIFICACION)
     * 6 = Desmonte de Cilindro -> (NO SE REALIZA EL SERVICIO)
     * 7 = Activación de chip (Anual) ->  (NO SE REALIZA EL SERVICIO)
     * 8 = Duplicado GNV -> (SERVICIO GNV)
     * 9 = Duplicado GLP ->  (NO SE REALIZA EL SERVICIO)
     * 10 = Conversión a GNV + Chip -> (SERVICIO GNV)
     * 11 = Chip por deterioro-> (SI SE REALIZA EL SERVICIO PERO NO SE GENERA CERTIFICADO, POR ENDE NO TIENE NUMSERIE)
     * 12 = Pre-inicial GNV -> (SERVICIO GNV)
     * 13 = Pre-inicial GLP -> (SERVICIO GLP)
     * 14 = Conversión a GNV OVERHUL -> (SERVICIO GNV)
     */
    
}
