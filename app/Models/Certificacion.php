<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Certificacion extends Model
{
    use HasFactory;

    protected $table = "certificacion";

    public $fillable = [
        "id",
        "idVehiculo",
        "idTaller",
        "idInspector",
        "idServicio",
        // "idServicioMaterial", No existe el campo idServicioMaterial en la tabla certificacion, es una tabla intermedia 'serviciomaterial' que guarda idCertificacion y idMaterial
        "estado", // 1=certificado, 2=anulado, 3=pendiente
        "precio",
        "pagado", // 0=pendiente de pago, 1=genero pendiente activacionchip, 2=pago realizado
        "idDuplicado",
        "idTallerAuto",
        "externo", // 0=interno, 1=externo
        "placaantigua",
        "descripcion",
        "fechaCertificado",
        "numSerie",
        "anioSerie",
        "created_at",
        "updated_at",
    ];

    protected $appends = [
        'serie_formato', // serie del material relacion Materiales
        'placa',
        'tipo_servicio',
        'ruta_vista_certificado',
        'ruta_descarga_certificado',
        'ruta_vista_ft',
        'ruta_descarga_ft',
        'fecha_documento', // <-- Atributo virtual para los PDF
        'serie_certificado_completa', // serie del certificado
    ];



    public function Vehiculo()
    {
        return $this->belongsTo(vehiculo::class, 'idVehiculo');
    }

    public function Taller()
    {
        return $this->belongsTo(Taller::class, 'idTaller');
    }

    public function TallerAuto()
    {
        return $this->belongsTo(Taller::class, 'idTallerAuto');
    }

    public function Duplicado()
    {
        return $this->belongsTo(Duplicado::class, 'idDuplicado');
    }

    public function Inspector()
    {
        return $this->belongsTo(User::class, 'idInspector');
    }

    public function Servicio()
    {
        return $this->belongsTo(Servicio::class, 'idServicio');
    }

    public function Materiales()
    {
        return $this->belongsToMany(Material::class, 'serviciomaterial', 'idCertificacion', 'idMaterial');
    }

    // relacion con modificacion para distinguir una modi normal de una modi de motor (YA NO SE USA, EN SU REEMPLAZO certificacion.descripcion)
    /*public function modificacionDetalle()
    {
        return $this->hasOne(ModificacionDetalle::class);
    }*/


    //scopes para busquedas
    public function scopeNumFormato($query, $search): void
    {
        if ($search) {
            $query->whereHas('Materiales', function (Builder $query) use ($search) {
                $query->where('numSerie', 'like', '%' . $search . '%');
            });
        }
    }
    public function scopePlacaVehiculo($query, $search): void
    {
        if ($search) {
            $query->orWhereHas('Vehiculo', function (Builder $query) use ($search) {
                $query->where('placa', 'like', '%' . $search . '%');
            });
        }
    }
    public function scopeNumSerieVehiculo($query, $search): void
    {
        if ($search) {
            $query->orWhereHas('Vehiculo', function (Builder $query) use ($search) {
                $query->where('numSerie', 'like', '%' . $search . '%');
            });
        }
    }
    public function scopeIdInspector(Builder $query, string $search): void
    {
        if ($search) {
            $query->where('idInspector', $search);
        }
    }
    public function scopeRangoFecha(Builder $query, string $desde, string $hasta): void
    {
        if ($desde && $hasta) {
            $query->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59']);
        }
    }

    public function scopeTipoServicio($query, $search): void
    {
        if ($search) {
            $query->whereHas('Servicio', function (Builder $query) use ($search) {
                $query->where('tipoServicio_idtipoServicio', $search);
            });
        }
    }

    public function scopeIdTaller($query, $search): void
    {
        if ($search) {
            $query->whereHas('Taller', function (Builder $query) use ($search) {
                $query->where('id', $search);
            });
        }
    }

    public function scopeIdTalleres($query, $search): void
    {
        if ($search) {
            $query->whereHas('Taller', function (Builder $query) use ($search) {
                $query->whereIn('id', $search);
            });
        }
    }

    public function scopeIdInspectores(Builder $query, $search): void
    {
        if ($search) {
            $query->whereIn('idInspector', $search);
        }
    }
    public function scopeIdTipoServicio($query, $search): void
    {
        if ($search) {
            $query->whereHas('Servicio', function (Builder $query) use ($search) {
                $query->where('tipoServicio_idtipoServicio', $search);
            });
        }
    }

    //Scope para reporte  
    public function scopePagado($query)
    {
        return $query->where('pagado', 0);
    }

    public function scopeEstado($query)
    {
        return $query->whereIn('estado', [3, 1]);
    }

    public function scopeFiltrarPorFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
    }


    //Atributos Especiales del Certificado

    public function getplacaAttribute()
    {
        return $this->Vehiculo->placa;
    }

    public function gettipoServicioAttribute()
    {
        return $this->Servicio->tipoServicio->id;
    }

    //cambie esto por lo de abajo
    /*
    public function getserieFormatoAttribute(){
        //$hoja=Certificacion::find($this->attributes['id'])->Materiales->where('idTipoMaterial',1)->first();
        //return $hoja;
        $serie=null;

            $numero=$this->Materiales->where('idTipoMaterial',1)->first()->numSerie;


        if($numero){
            $serie=$numero;
        }
        return $serie;

    }*/

    public function getserieFormatoAttribute()
    {
        $serie = null;

        $material = $this->Materiales->where('idTipoMaterial', 1)->first();

        if ($material) {
            $serie = $material->numSerie;
        }

        return $serie;
    }

    public function getserieFormatoGLPAttribute()
    {
        $serie = null;

        $material = $this->Materiales->where('idTipoMaterial', 3)->first();

        if ($material) {
            $serie = $material->numSerie;
        }

        return $serie;
    }

    /*public function getHojaAttribute()
    {
        $idServicio = $this->Servicio->tipoServicio->id;
        $hoja = null;
        if (in_array($idServicio, [1, 2, 7, 8, 10, 12, 14])) {
            $hoja = Certificacion::find($this->attributes['id'])->Materiales->where('idTipoMaterial', 1)->first();
            return $hoja;
        } elseif (in_array($idServicio, [3, 4, 9, 13])) {
            $hoja = Certificacion::find($this->attributes['id'])->Materiales->where('idTipoMaterial', 3)->first();
            return $hoja;
        } elseif (in_array($idServicio, [5])) {
            $hoja = Certificacion::find($this->attributes['id'])->Materiales->where('idTipoMaterial', 4)->first();
            return $hoja;
        } else {
            return $hoja;
        }
    }*/
    public function getHojaAttribute()
    {
        // Validación de seguridad por si el registro se consulta tras ser eliminado
        if (!isset($this->attributes['id'])) {
            return null;
        }

        // Accedemos a la relación cargada en el servicio de forma segura
        $idTipoServicio = $this->Servicio->tipoServicio_idtipoServicio ?? null;
        $hoja = null;

        if (in_array($idTipoServicio, [1, 2, 7, 8, 10, 12, 14])) {
            // Usamos $this->Materiales directamente sin hacer Certificacion::find()
            $hoja = $this->Materiales->where('idTipoMaterial', 1)->first();
        } elseif (in_array($idTipoServicio, [3, 4, 9, 13])) {
            $hoja = $this->Materiales->where('idTipoMaterial', 3)->first();
        } elseif ($idTipoServicio == 5) {
            $hoja = $this->Materiales->where('idTipoMaterial', 4)->first();
        }

        return $hoja;
    }

    /*public function getNumHojaAttribute()
    {
        $idServicio = $this->Servicio->tipoServicio->id;
        $hoja = null;
        if (in_array($idServicio, [1, 2, 7, 8, 10, 12, 14])) {
            $hoja = Certificacion::find($this->attributes['id'])->Materiales->where('idTipoMaterial', 1)->first()->numSerie;
            return $hoja;
        } elseif (in_array($idServicio, [3, 4, 9, 13])) {
            $hoja = Certificacion::find($this->attributes['id'])->Materiales->where('idTipoMaterial', 3)->first()->numSerie;
            return $hoja;
        } elseif (in_array($idServicio, [5])) {
            $hoja = Certificacion::find($this->attributes['id'])->Materiales->where('idTipoMaterial', 4)->first()->numSerie;
            return $hoja;
        } else {
            return $hoja;
        }
    }*/
    public function getNumHojaAttribute()
    {
        // Validación de seguridad por si el registro se consulta tras ser eliminado
        if (!isset($this->attributes['id'])) {
            return null;
        }

        $hoja = $this->getHojaAttribute(); // Reutilizamos el método anterior para no repetir código

        return $hoja ? $hoja->numSerie : null;
    }

    /*public function getUbicacionHojaAttribute()
    {
        $idServicio = $this->Servicio->tipoServicio->id;
        $hoja = null;
        if (in_array($idServicio, [1, 2, 7, 8, 10, 12, 14])) {
            $hoja = Certificacion::find($this->attributes['id'])->Materiales->where('idTipoMaterial', 1)->first()->ubicacion;
            return $hoja;
        } elseif (in_array($idServicio, [11])) {
            $hoja = Certificacion::find($this->attributes['id'])->Materiales->where('idTipoMaterial', 2)->first()->ubicacion;;
            return $hoja;
        } elseif (in_array($idServicio, [3, 4, 9, 13])) {
            $hoja = Certificacion::find($this->attributes['id'])->Materiales->where('idTipoMaterial', 3)->first()->ubicacion;;
            return $hoja;
        } elseif (in_array($idServicio, [5])) {
            $hoja = Certificacion::find($this->attributes['id'])->Materiales->where('idTipoMaterial', 4)->first()->ubicacion;;
            return $hoja;
        } else {
            return $hoja;
        }
    }*/
    public function getUbicacionHojaAttribute()
    {
        $idServicio = $this->Servicio->tipoServicio->id;
        $material = null;

        if (in_array($idServicio, [1, 2, 7, 8, 10, 12, 14])) {
            $material = $this->Materiales->where('idTipoMaterial', 1)->first();
        } elseif ($idServicio == 11) {
            $material = $this->Materiales->where('idTipoMaterial', 2)->first();
        } elseif (in_array($idServicio, [3, 4, 9, 13])) {
            $material = $this->Materiales->where('idTipoMaterial', 3)->first();
        } elseif ($idServicio == 5) {
            $material = $this->Materiales->where('idTipoMaterial', 4)->first();
        }

        return $material ? $material->ubicacion : null;
    }


    /**
     * Accesor para obtener la fecha del PDF.
     * Si 'fechaCertificado' tiene valor, usa ese; si es null, usa 'created_at'.
     */
    public function getFechaDocumentoAttribute()
    {
        if (!empty($this->attributes['fechaCertificado'])) {
            return \Carbon\Carbon::parse($this->attributes['fechaCertificado']);
        }

        return \Carbon\Carbon::parse($this->attributes['created_at']);
    }

    /**
     * Obtiene la serie formateada para el PDF (Ejemplo: GNV-2026-00045)
     */
    public function getSerieCertificadoCompletaAttribute()
    {
        if (!$this->numSerie || !$this->anioSerie) {
            return 'SIN SERIE';
        }

        // Determinamos el prefijo según el tipo de servicio
        $idTipoServicio = $this->Servicio->tipoServicio_idtipoServicio;
        $prefijo = '';

        if (in_array($idTipoServicio, [1, 2, 8, 10, 12, 14])) {
            $prefijo = 'GNV';
        } elseif (in_array($idTipoServicio, [3, 4, 13])) {
            $prefijo = 'GLP';
        } elseif ($idTipoServicio == 5) {
            $prefijo = 'MOD';
        }

        return $prefijo . '-' . $this->anioSerie . '-' . str_pad($this->numSerie, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Función estática para generar el siguiente número correlativo por grupo y año
     */
    /*public static function obtenerSiguienteCorrelativo($idServicio, $fechaCertificacion)
    {
        // Obtener el id del tipo de servicio asignado
        $servicio = Servicio::findOrFail($idServicio);
        $idTipoServicio = $servicio->tipoServicio_idtipoServicio;

        // Si es chip por deterioro (Caso 11), no genera serie ni correlativo
        if ($idTipoServicio == 11) {
            return ['numSerie' => null, 'anioSerie' => null];
        }

        // Definir el grupo de servicios hermanos
        $serviciosGrupo = [];
        if (in_array($idTipoServicio, [1, 2, 7, 8, 10, 12, 14])) {
            $serviciosGrupo = [1, 2, 7, 8, 10, 12, 14]; // Grupo servicios GNV
        } elseif (in_array($idTipoServicio, [3, 4, 13])) {
            $serviciosGrupo = [3, 4, 13];             // Grupo servicios GLP
        } elseif ($idTipoServicio == 5) {
            $serviciosGrupo = [5];                    // Grupo servicios Modificación
        }

        // Extraer el año de la fecha de certificación elegida
        $anio = Carbon::parse($fechaCertificacion)->year;

        // Buscar el último registro del mismo grupo creado en ese mismo año
        $ultimoCertificado = self::whereIn('idServicio', function($query) use ($serviciosGrupo) {
                                    $query->select('id')
                                          ->from('servicio')
                                          ->whereIn('tipoServicio_idtipoServicio', $serviciosGrupo);
                                })
                                ->where('anioSerie', $anio)
                                ->whereNotNull('numSerie')
                                ->orderBy('numSerie', 'desc')
                                ->first();

        // Si existe, sumamos 1. Si no existe (es año nuevo o primer registro), arranca en 1.
        $siguienteCorrelativo = $ultimoCertificado ? ($ultimoCertificado->numSerie + 1) : 1;

        return [
            'numSerie' => $siguienteCorrelativo,
            'anioSerie' => $anio
        ];
    }*/
    public static function obtenerSiguienteCorrelativo($idServicio, $fechaCertificacion)
    {
        $servicio = Servicio::findOrFail($idServicio);
        $idTipoServicio = $servicio->tipoServicio_idtipoServicio;

        if ($idTipoServicio == 11) {
            return ['numSerie' => null, 'anioSerie' => null];
        }

        $serviciosGrupo = [];
        $nombreGrupo = null;
        if (in_array($idTipoServicio, [1, 2, 7, 8, 10, 12, 14])) {
            $serviciosGrupo = [1, 2, 7, 8, 10, 12, 14];
            $nombreGrupo = 'GNV';
        } elseif (in_array($idTipoServicio, [3, 4, 13])) {
            $serviciosGrupo = [3, 4, 13];
            $nombreGrupo = 'GLP';
        } elseif ($idTipoServicio == 5) {
            $serviciosGrupo = [5];
            $nombreGrupo = 'MOD';
        }

        // Si por alguna razón no pertenece a ningún grupo controlado, no genera correlativo
        if (is_null($nombreGrupo)) {
            return ['numSerie' => null, 'anioSerie' => null];
        }

        $anio = Carbon::parse($fechaCertificacion)->year;

        // CONTROL DE HUECOS SERIE DEL CERTIFICADO UTILIZANDO LA TABLA ELIMINACION
        // Buscamos si existe alguna serie aprobada por desistimiento (estado = 2) para este grupo y año
        $serieHueco = DB::table('eliminacion')
                        ->where('grupoServicio', $nombreGrupo)
                        ->where('anioSerie', $anio)
                        ->where('estado', 2) // Solo las aprobadas y listas para reuso
                        ->orderBy('numSerie', 'asc') // Tomamos el hueco más antiguo primero para mantener orden
                        ->first();

        if ($serieHueco) {
            // Marcamos este registro como consumido (estado = 3) para que otro inspector en paralelo no lo vuelva a agarrar
            DB::table('eliminacion')
                ->where('id', $serieHueco->id)
                ->update(['estado' => 3]);

            return [
                'numSerie' => $serieHueco->numSerie,
                'anioSerie' => $serieHueco->anioSerie
            ];
        }

        // Si no hay huecos Buscar el último registro del mismo grupo creado en ese mismo año
        $ultimoCertificado = self::whereIn('idServicio', function($query) use ($serviciosGrupo) {
                                    $query->select('id')
                                        ->from('servicio')
                                        ->whereIn('tipoServicio_idtipoServicio', $serviciosGrupo);
                                })
                                ->where('anioSerie', $anio)
                                ->whereNotNull('numSerie')
                                ->orderBy('numSerie', 'desc')
                                ->first();
        // Si existe, sumamos 1. Si no existe (es año nuevo o primer registro), arranca en 1.
        $siguienteCorrelativo = $ultimoCertificado ? ($ultimoCertificado->numSerie + 1) : 1;

        return [
            'numSerie' => $siguienteCorrelativo,
            'anioSerie' => $anio
        ];
    }



    public function getChipMaterialAttribute()
    {
        $chip = Certificacion::find($this->attributes['id'])->Materiales->where('idTipoMaterial', 2)->first();
        return $chip;
    }
    
    public function getChipAttribute()
    {
        return $this->Vehiculo->Equipos->where('idTipoEquipo', 1)->first();
    }

    public function getReductorAttribute()
    {
        return $this->Vehiculo->Equipos->where('idTipoEquipo', 2)->first();
    }

    public function getReductorGlpAttribute()
    {
        return $this->Vehiculo->Equipos->where('idTipoEquipo', 4)->first();
    }

    public function getCilindrosAttribute()
    {
        return $this->Vehiculo->Equipos->where('idTipoEquipo', 3);
    }

    public function getCilindrosGlpAttribute()
    {
        return $this->Vehiculo->Equipos->where('idTipoEquipo', 5);
    }

    public function getRutaVistaCertificadoAttribute()
    {
        $ruta = null;
        switch ($this->Servicio->tipoServicio->id) {
            case 1: //tipo servicio = inicial gnv
                $ruta = route('certificadoInicialGnv', ['id' => $this->attributes['id']]);
                break;
            case 2: //tipo servicio = anual gnv
                $ruta = route('certificadoAnualGnv', ['id' => $this->attributes['id']]);
                break;
            case 3: //tipo servicio = inicial gnv
                $ruta = route('certificadoInicialGlp', ['id' => $this->attributes['id']]);
                break;
            case 4: //tipo servicio = anual gnv
                $ruta = route('certificadoAnualGlp', ['id' => $this->attributes['id']]);
                break;
            case 5: //tipo servicio = modificacion
                $ruta = route('certificadoModificacion', ['id' => $this->attributes['id']]);
                break;

            case 8: //tipo servicio = anual gnv
                $dupli = Duplicado::find($this->attributes["idDuplicado"]);
                if ($dupli) {
                    $ruta = $this->generaRutaDuplicado($dupli);
                } else {
                    $ruta = null;
                }
                break;
            case 9: //tipo servicio = DUPLICADO GLP
                $dupli = Duplicado::find($this->attributes["idDuplicado"]);
                if ($dupli) {
                    $ruta = $this->generaRutaDuplicado($dupli);
                } else {
                    $ruta = null;
                }
                break;
            case 10: //tipo servicio = inicial gnv + chip
                $ruta = route('certificadoInicialGnv', ['id' => $this->attributes['id']]);
                break;

            case 12: //tipo servicio = Preconver
                $ruta = route('generaPreGnvPdf', ['id' => $this->attributes['id']]);
                break;
            case 13: //tipo servicio = Preconver
                $ruta = route('generaPreGlpPdf', ['id' => $this->attributes['id']]);
                break;
            case 14: //tipo servicio = inicial gnv overhul
                $ruta = route('certificadoInicialGnvOverhul', ['id' => $this->attributes['id']]);
                break;

            default:
                $ruta = null;
                break;
        }

        return $ruta;
    }

    public function getRutaDescargaCertificadoAttribute()
    {
        $ruta = null;
        switch ($this->Servicio->tipoServicio->id) {
            case 1: //tipo servicio = inicial gnv
                $ruta = route('descargarCertificadoInicialGnv', ['id' => $this->attributes['id']]);
                break;
            case 2: //tipo servicio = anual gnv
                $ruta = route('descargarCertificadoAnualGnv', ['id' => $this->attributes['id']]);
                break;
            case 3: //tipo servicio = anual glp
                $ruta = route('descargarCertificadoInicialGlp', ['id' => $this->attributes['id']]);
                break;
            case 4: //tipo servicio = anual glp
                $ruta = route('descargarCertificadoAnualGlp', ['id' => $this->attributes['id']]);
                break;
            case 5: //tipo servicio = modificacion
                $ruta = route('descargarCertificadoModificacion', ['id' => $this->attributes['id']]);
                break;

            case 8: //tipo servicio = anual gnv
                $dupli = Duplicado::find($this->attributes["idDuplicado"]);
                if ($dupli) {
                    $ruta = $this->generaRutaDescargaDuplicado($dupli);
                } else {
                    $ruta = null;
                }
                break;
            case 9: //tipo servicio = DUPLICADO GLP
                $dupli = Duplicado::find($this->attributes["idDuplicado"]);
                if ($dupli) {
                    $ruta = $this->generaRutaDescargaDuplicado($dupli);
                } else {
                    $ruta = null;
                }
                break;
            case 10: //tipo servicio = inicial gnv + chip
                $ruta = route('descargarCertificadoInicialGnv', ['id' => $this->attributes['id']]);
                break;

            case 12: //tipo servicio = preconversion
                $ruta = route('descargarPreGnvPdf', ['id' => $this->attributes['id']]);
                break;
            case 13: //tipo servicio = preconversion
                $ruta = route('descargarPreGlpPdf', ['id' => $this->attributes['id']]);
                break;
            case 14: //tipo servicio = inicial gnv overhul
                $ruta = route('descargarCertificadoInicialGnvOverhul', ['id' => $this->attributes['id']]);
                break;


            default:
                $ruta = null;
                break;
        }

        return $ruta;
    }
    public function getRutaVistaCheckListArribaAttribute()
    {
        $ruta = null;
        switch ($this->Servicio->tipoServicio->id) {
            case 1: //tipo servicio = inicial gnv
                $ruta = route('checkListArribaGnv', ['idCert' => $this->attributes['id']]);
                break;
            case 2: //tipo servicio = anual gnv
                $ruta = route('checkListArribaGnv', ['idCert' => $this->attributes['id']]);
                break;
            case 3: //tipo servicio = inicial glp
                $ruta = route('checkListArribaGlp', ['idCert' => $this->attributes['id']]);
                break;
            case 4: //tipo servicio = anual glp
                $ruta = route('checkListArribaGlp', ['idCert' => $this->attributes['id']]);
                break;
            case 10: //tipo servicio = inicial gnv + chip
                $ruta = route('checkListArribaGnv', ['idCert' => $this->attributes['id']]);
                break;

            case 12: //tipo servicio = preconversion
                $ruta = route('checkListArribaGnv', ['idCert' => $this->attributes['id']]);
                break;
            case 13: //tipo servicio = preconversion
                $ruta = route('checkListArribaGlp', ['idCert' => $this->attributes['id']]);
                break;
            case 14: //tipo servicio = inicial gnv overhul
                $ruta = route('checkListArribaGnv', ['idCert' => $this->attributes['id']]);
                break;
            default:
                $ruta = null;
                break;
        }

        return $ruta;
    }

    public function getRutaVistaCheckListAbajoAttribute()
    {
        $ruta = null;
        switch ($this->Servicio->tipoServicio->id) {
            case 1: //tipo servicio = inicial gnv
                $ruta = route('checkListAbajoGnv', ['idCert' => $this->attributes['id']]);
                break;
            case 2: //tipo servicio = anual gnv
                $ruta = route('checkListAbajoGnv', ['idCert' => $this->attributes['id']]);
                break;
            case 3: //tipo servicio = inicial glp
                $ruta = route('checkListAbajoGlp', ['idCert' => $this->attributes['id']]);
                break;
            case 4: //tipo servicio = anual glp
                $ruta = route('checkListAbajoGlp', ['idCert' => $this->attributes['id']]);
                break;
            case 10: //tipo servicio = inicial gnv + chip
                $ruta = route('checkListAbajoGnv', ['idCert' => $this->attributes['id']]);
                break;

            case 12: //tipo servicio = preconversion
                $ruta = route('checkListAbajoGnv', ['idCert' => $this->attributes['id']]);
                break;
            case 13: //tipo servicio = preconversion
                $ruta = route('checkListAbajoGlp', ['idCert' => $this->attributes['id']]);
                break;
            case 14: //tipo servicio = inicial gnv overhul
                $ruta = route('checkListAbajoGnv', ['idCert' => $this->attributes['id']]);
                break;
            default:
                $ruta = null;
                break;
        }

        return $ruta;
    }

    public function generaRutaDuplicado(Duplicado $duplicado)
    {
        $ruta = null;

        switch ($duplicado->externo) {
            case 0:
                switch ($duplicado->servicio) {

                    case 1:
                        $ruta = route('duplicadoInicialGnv', ['id' => $this->attributes['id']]);
                        break;
                    case 2:
                        $ruta = route('duplicadoAnualGnv', ['id' => $this->attributes['id']]);
                        break;
                    //AGREGAR CASE 3 Y 4 PARA GLPS
                    case 3:
                        $ruta = route('duplicadoInicialGlp', ['id' => $this->attributes['id']]);
                        break;
                    case 4:
                        $ruta = route('duplicadoAnualGlp', ['id' => $this->attributes['id']]);
                        break;
                }
                break;
            case 1:
                switch ($duplicado->servicio) {

                    case 1:
                        $ruta = route('duplicadoExternoInicialGnv', ['id' => $this->attributes['id']]);
                        break;
                    case 2:
                        $ruta = route('duplicadoExternoAnualGnv', ['id' => $this->attributes['id']]);
                        break;
                    //AGREGAR CASE 3 Y 4 PARA GLPS
                    case 3:
                        $ruta = route('duplicadoExternoInicialGlp', ['id' => $this->attributes['id']]);
                        break;
                    case 4:
                        $ruta = route('duplicadoExternoAnualGlp', ['id' => $this->attributes['id']]);
                        break;
                }

                break;

            default:
                # code...
                break;
        }

        return $ruta;
    }

    public function generaRutaDescargaDuplicado(Duplicado $duplicado)
    {
        $ruta = null;

        switch ($duplicado->externo) {
            case 0:
                switch ($duplicado->servicio) {

                    case 1:
                        $ruta = route('descargarDuplicadoInicialGnv', ['id' => $this->attributes['id']]);
                        break;
                    case 2:
                        $ruta = route('descargarDuplicadoAnualGnv', ['id' => $this->attributes['id']]);
                        break;
                        //AGREGAR CASE 3 Y 4 PARA GLPS
                }
                break;
            case 1:
                switch ($duplicado->servicio) {

                    case 1:
                        $ruta = route('descargarDuplicadoExternoInicialGnv', ['id' => $this->attributes['id']]);
                        break;
                    case 2:
                        $ruta = route('descargarDuplicadoExternoAnualGnv', ['id' => $this->attributes['id']]);
                        break;
                        //AGREGAR CASE 3 Y 4 PARA GLPS
                }

                break;

            default:
                # code...
                break;
        }

        return $ruta;
    }

    public function getRutaVistaFtAttribute()
    {
        $ruta = null;
        switch ($this->Servicio->tipoServicio->id) {
            case 1:
                $ruta = route('fichaTecnicaGnv', ['idCert' => $this->attributes['id']]);
                break;
            case 2:
                $ruta = route('fichaTecnicaGnv', ['idCert' => $this->attributes['id']]);
                break;
            case 3:
                $ruta = route('fichaTecnicaGlp', ['idCert' => $this->attributes['id']]);
                break;
            case 4:
                $ruta = route('fichaTecnicaGlp', ['idCert' => $this->attributes['id']]);
                break;
            case 10:
                $ruta = route('fichaTecnicaGnv', ['idCert' => $this->attributes['id']]);
                break;
            case 12:
                $ruta = route('fichaTecnicaGnv', ['idCert' => $this->attributes['id']]);
                break;
            case 13:
                $ruta = route('fichaTecnicaGlp', ['idCert' => $this->attributes['id']]);
                break;
            case 14:
                $ruta = route('fichaTecnicaGnv', ['idCert' => $this->attributes['id']]);
                break;
            default:
                $ruta = null;
                break;
        }

        return $ruta;
    }

    public function getRutaDescargaFtAttribute()
    {
        $ruta = null;
        switch ($this->Servicio->tipoServicio->id) {
            case 1:
                $ruta = route('descargarFichaTecnicaGnv', ['idCert' => $this->attributes['id']]);
                break;
            case 2:
                $ruta = route('descargarFichaTecnicaGnv', ['idCert' => $this->attributes['id']]);
                break;
            case 3:
                $ruta = route('descargarFichaTecnicaGlp', ['idCert' => $this->attributes['id']]);
                break;
            case 4:
                $ruta = route('descargarFichaTecnicaGlp', ['idCert' => $this->attributes['id']]);
                break;
            case 10:
                $ruta = route('descargarFichaTecnicaGnv', ['idCert' => $this->attributes['id']]);
                break;
            case 12:
                $ruta = route('descargarFichaTecnicaGnv', ['idCert' => $this->attributes['id']]);
                break;
            case 13:
                $ruta = route('descargarFichaTecnicaGlp', ['idCert' => $this->attributes['id']]);
                break;
            case 14:
                $ruta = route('fichaTecnicaGnv', ['idCert' => $this->attributes['id']]);
                break;
            default:
                $ruta = null;
                break;
        }

        return $ruta;
    }

    public function getCalculaPesosAttribute()
    {
        $equipos = 0;
        if ($this->Servicio->tipoServicio->id == 3 && $this->Vehiculo->combustible == 'BI-COMBUSTIBLE GLP') {
            return 30;
        }

        $equipos = $this->Vehiculo->Equipos->where('idTipoEquipo', 3);

        return $equipos->sum('peso');
    }

    // FUNCION PARA CERTIFICAR SERVICIOS GNV
    public static function certificarGnv(Taller $taller, Servicio $servicio, Material $hoja, vehiculo $vehiculo, User $inspector, $externoValue, $placaantigua, $fechaCertificado)
    {
        //Condicion para jalar el precio de la tabla servicios si certificacion es de taller o precios_inspector si es externo es decir inspector externo
        if ($externoValue == 0) {
            $precio = $servicio->precio;
        } elseif ($externoValue == 1) {
            $precio = PrecioInspector::where([['idServicio', $servicio->TipoServicio->id], ['idUsers', $inspector->id]])->first();
            $precio = $precio ? $precio->precio : 0;
        }

        $datosSerie = self::obtenerSiguienteCorrelativo($servicio->id, $fechaCertificado);

        $cert = Certificacion::create([
            "idVehiculo" => $vehiculo->id,
            "idTaller" => $taller->id,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio->id,
            "estado" => 1,
            "precio" => $precio,
            "pagado" => 0,
            "externo" => $externoValue,
            "placaantigua" => $placaantigua,
            "fechaCertificado" => $fechaCertificado,
            "numSerie"         => $datosSerie['numSerie'],
            "anioSerie"        => $datosSerie['anioSerie'],
        ]);
        if ($cert) {
            //cambia el estado de la hoja a consumido
            $hoja->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            //crea y guarda el servicio y material usado en esta certificacion
            $servM = ServicioMaterial::create([
                "idMaterial" => $hoja->id,
                "idCertificacion" => $cert->id
            ]);
            //retorna el certificado
            return $cert;
        } else {
            return null;
        }
    }

    // FUNCION PARA CERTIFICAR SERVICIOS GLP
    public static function certificarGlp(Taller $taller, Taller $tallerAuto, Servicio $servicio, Material $hoja, vehiculo $vehiculo, User $inspector, $externoValue, $fechaCertificado)
    {
        // Buscar idSalida en SalidaDetalle con el idMaterial
        $idSalida = optional(SalidaDetalle::where('idMaterial', $hoja->id)->first())->idSalida;

        // Validar si idSalida existe en Contado
        $esContado = Contado::where('idSalida', $idSalida)->exists();

        // Si el material fue vendido al contado, el precio es 0, de lo contrario, se calcula normalmente
        $precio = $esContado ? 0
            : ($externoValue == 0 ? $servicio->precio : optional(PrecioInspector::where([['idServicio', $servicio->TipoServicio->id], ['idUsers', $inspector->id]])->first())->precio ?? 0);

        // Asignar el valor de pagado
        $pagado = $esContado ? 2 : 0;

        $datosSerie = self::obtenerSiguienteCorrelativo($servicio->id, $fechaCertificado);

        $cert = Certificacion::create([
            "idVehiculo" => $vehiculo->id,
            "idTaller" => $taller->id,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio->id,
            "estado" => 1,
            "precio" => $precio,
            "pagado" => $pagado,
            "idTallerAuto" => $tallerAuto->id,
            "externo" => $externoValue,
            "fechaCertificado" => $fechaCertificado,
            "numSerie"         => $datosSerie['numSerie'],
            "anioSerie"        => $datosSerie['anioSerie'],
        ]);
        
        if ($cert) {
            //cambia el estado de la hoja a consumido
            $hoja->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            //crea y guarda el servicio y material usado en esta certificacion
            $servM = ServicioMaterial::create([
                "idMaterial" => $hoja->id,
                "idCertificacion" => $cert->id
            ]);
            //retorna el certificado
            return $cert;
        } else {
            return null;
        }
    }

    // FUNCION PARA CERTIFICAR MODIFICACION
    public static function certificarModi(Taller $taller, Servicio $servicio, Material $hoja, vehiculo $vehiculo, User $inspector, $externoValue, $esModiMotor = false, $fechaCertificado)
    {
        // Paso 1: Buscar idSalida en SalidaDetalle con el idMaterial
        $idSalida = optional(SalidaDetalle::where('idMaterial', $hoja->id)->first())->idSalida;
        // Paso 2: Validar si idSalida existe en Contado
        $esContado = Contado::where('idSalida', $idSalida)->exists();

        // Si es modificación de motor, forzar precio a 140
        if ($esModiMotor) {
            $precio = 140;
        } else {
            // Si el material fue vendido al contado, el precio es 0, de lo contrario, se calcula normalmente
            $precio = $esContado ? 0
                : ($externoValue == 0 ? $servicio->precio : optional(PrecioInspector::where([['idServicio', $servicio->TipoServicio->id], ['idUsers', $inspector->id]])->first())->precio ?? 0);
        }
        
        // Asignar el valor de pagado
        $pagado = $esContado ? 2 : 0;

        $datosSerie = self::obtenerSiguienteCorrelativo($servicio->id, $fechaCertificado);

        $cert = Certificacion::create([
            "idVehiculo" => $vehiculo->id,
            "idTaller" => $taller->id,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio->id,
            "estado" => 1,
            "precio" => $precio,
            "pagado" => $pagado,
            "externo" => $externoValue, //agregamos el nuevo campo externo
            "descripcion" => $esModiMotor ? "Modificación Motor" : null,
            "fechaCertificado" => $fechaCertificado,
            "numSerie"         => $datosSerie['numSerie'],
            "anioSerie"        => $datosSerie['anioSerie'],
        ]);
        if ($cert) {
            //cambia el estado de la hoja a consumido
            $hoja->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            //crea y guarda el servicio y material usado en esta certificacion
            $servM = ServicioMaterial::create([
                "idMaterial" => $hoja->id,
                "idCertificacion" => $cert->id
            ]);
            //retorna el certificado
            return $cert;
        } else {
            return null;
        }
    }

    // FUNCION PARA CERTIFICAR DETERIORO DE CHIP
    public static function certificarChipDeterioro($taller,  $servicio, Material $chip,  User $inspector, $nombre, $placa, $externoValue, $fechaCertificado)
    {
        // Buscamos el servicio una sola vez
        $servicioModel = Servicio::findOrFail($servicio);

        // Condición para jalar el precio de la tabla servicios o precios_inspector
        if ($externoValue == 0) {
            $precio = $servicioModel->precio;
        } elseif ($externoValue == 1) {$precio = PrecioInspector::where([['idServicio', $servicioModel->tipoServicio->id], ['idUsers', $inspector->id]])->first();            
            $precio = $precio ? $precio->precio : 0;
        }

        $cert = Certificacion::create([
            "idVehiculo" => 1, // Vehículo comodín por defecto para deterioro
            "idTaller" => $taller,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio,
            "estado" => 1,
            "precio" => $precio,
            "pagado" => 0,
            "externo" => $externoValue,
            "descripcion" => "$nombre / $placa",
            "fechaCertificado" => $fechaCertificado,
        ]);
        if ($cert) {
            //cambia el estado de la hoja a consumido
            // $chip->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            $chip->update([
                "estado" => 4, 
                "ubicacion" => "En poder del cliente " . $nombre . "/" . $placa, 
                "descripcion" => "Chip consumido por deterioro"
            ]);

            //crea y guarda el servicio y material usado en esta certificacion
            $servM = ServicioMaterial::create([
                "idMaterial" => $chip->id,
                "idCertificacion" => $cert->id
            ]);
            //retorna el certificado
            return $cert;
        } else {
            return null;
        }
    }
    // FUNCION PARA TRAMITAR DETERIORO DE CHIP (SIN CREAR SERVICIO MATERIAL)
    public static function tramiteChipDeterioro($taller, $servicio, User $inspector, $nombre, $placa, $externoValue, $fechaCertificado)
    {
        // Buscamos el servicio una sola vez
        $servicioModel = Servicio::findOrFail($servicio);

        if ($externoValue == 0) {
            $precio = $servicioModel->precio;
        } elseif ($externoValue == 1) {
            $precio = PrecioInspector::where([['idServicio', $servicioModel->tipoServicio->id], ['idUsers', $inspector->id]])->first();            
            $precio = $precio ? $precio->precio : 0;
        }

        return Certificacion::create([
            "idVehiculo" => 1,
            "idTaller" => $taller,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio,
            "estado" => 1,
            "precio" => $precio,
            "pagado" => 0,
            "externo" => $externoValue,
            "descripcion" => "TRÁMITE: $nombre / $placa",
            "fechaCertificado" => $fechaCertificado,
        ]);
    }


    public static function certificarDesmonte($taller,  $servicio,  User $inspector, $placa, $externoValue)
    {
        if ($externoValue == 0) {
            $precio = Servicio::find($servicio)->precio;
        } elseif ($externoValue == 1) {
            //$precio = PrecioInspector::where([['idServicio', Servicio::find($servicio)->TipoServicio->id], ['idUsers', $inspector->id]])->first()->precio;
            $precio = PrecioInspector::where([
                ['idServicio', Servicio::find($servicio)->TipoServicio->id],
                ['idUsers', $inspector->id]
            ])->first();

            if ($precio) {
                $precio = $precio->precio;
            } else {
                $precio = 0;
            }
        }

        $cert = Desmontes::create([
            "placa" => $placa,
            "idTaller" => $taller,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio,
            "estado" => 1,
            "precio" => $precio,
            "pagado" => 0,
            "externo" => $externoValue,
        ]);
        return $cert;
    }


    // FUNCION PARA CERTIFICAR PRECONVERSION GNV (PREINICIAL)
    public static function certificarGnvPre(Taller $taller, Servicio $servicio, Material $hoja, vehiculo $vehiculo, User $inspector, $externoValue, $fechaCertificado)
    {
        //Condicion para jalar el precio de la tabla servicios o precios_inspector
        if ($externoValue == 0) {
            $precio = $servicio->precio;
        } elseif ($externoValue == 1) {
            $precio = PrecioInspector::where([['idServicio', $servicio->TipoServicio->id], ['idUsers', $inspector->id]])->first();
            $precio = $precio ? $precio->precio : 0;
        }

        $datosSerie = self::obtenerSiguienteCorrelativo($servicio->id, $fechaCertificado);

        $cert = Certificacion::create([
            "idVehiculo" => $vehiculo->id,
            "idTaller" => $taller->id,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio->id,
            "estado" => 3,
            "precio" => $precio,
            "pagado" => 0,
            "externo" => $externoValue,
            "fechaCertificado" => $fechaCertificado,
            "numSerie"         => $datosSerie['numSerie'],
            "anioSerie"        => $datosSerie['anioSerie'],
        ]);
        if ($cert) {
            //cambia el estado de la hoja a consumido
            $hoja->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            //crea y guarda el servicio y material usado en esta certificacion
            $servM = ServicioMaterial::create([
                "idMaterial" => $hoja->id,
                "idCertificacion" => $cert->id
            ]);
            //retorna el certificado
            return $cert;
        } else {
            return null;
        }
    }

    // FUNCION PARA COMPLETAR PENDIENTE (ACTIVACION DE CHIPS) Y PASARLO A ESTADO CERTIFICADO
    public static function certificarGnvPendiente(Taller $taller, Servicio $servicio, Material $hoja, vehiculo $vehiculo, User $inspector, $precio, $externoValue, $fechaCertificado)
    {  
        $tipoServicioId = 2; // ID del tipo de 'Revisión anual GNV'
        $defaultServicio = Servicio::where('taller_idtaller', $taller->id)
            ->where('tipoServicio_idtipoServicio', $tipoServicioId)
            ->first();
    
        if ($defaultServicio) {
            $idServicio = $defaultServicio->id;
        } else {
            return null;
        }

        $datosSerie = self::obtenerSiguienteCorrelativo($servicio->id, $fechaCertificado);

        $cert = Certificacion::create([
            "idVehiculo" => $vehiculo->id,
            "idTaller" => $taller->id,
            "idInspector" => $inspector->id,
            //"idServicio" => $servicio->id,
            "idServicio" => $idServicio,
            "estado" => 1,
            "precio" => $precio,
            "pagado" => 0,
            "externo" => $externoValue,
            "fechaCertificado" => $fechaCertificado,
            "numSerie"         => $datosSerie['numSerie'],
            "anioSerie"        => $datosSerie['anioSerie'],
        ]);
        if ($cert) {
            //cambia el estado de la hoja a consumido
            $hoja->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            //crea y guarda el servicio y material usado en esta certificacion
            $servM = ServicioMaterial::create([
                "idMaterial" => $hoja->id,
                "idCertificacion" => $cert->id
            ]);
            //retorna el certificado
            return $cert;
        } else {
            return null;
        }
    }

    // FUNCION PARA CERTIFICAR PRECONVERSION GLP (PREINICIAL)
    public static function certificarGlpPre(Taller $taller, Taller $tallerAuto, Servicio $servicio, Material $hoja, vehiculo $vehiculo, User $inspector, $externoValue, $fechaCertificado)
    {
        //Condicion para jalar el precio de la tabla servicios o precios_inspector
        if ($externoValue == 0) {
            $precio = $servicio->precio;
        } elseif ($externoValue == 1) {
            $precio = PrecioInspector::where([['idServicio', $servicio->TipoServicio->id], ['idUsers', $inspector->id]])->first();
            $precio = $precio ? $precio->precio : 0;
        }

        $datosSerie = self::obtenerSiguienteCorrelativo($servicio->id, $fechaCertificado);

        $cert = Certificacion::create([
            "idVehiculo" => $vehiculo->id,
            "idTaller" => $taller->id,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio->id,
            "estado" => 3,
            "precio" => $precio,
            "pagado" => 0,
            "idTallerAuto" => $tallerAuto->id,
            "externo" => $externoValue,
            "fechaCertificado" => $fechaCertificado,
            "numSerie"         => $datosSerie['numSerie'],
            "anioSerie"        => $datosSerie['anioSerie'],
        ]);
        if ($cert) {
            //cambia el estado de la hoja a consumido
            $hoja->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            //crea y guarda el servicio y material usado en esta certificacion
            $servM = ServicioMaterial::create([
                "idMaterial" => $hoja->id,
                "idCertificacion" => $cert->id
            ]);
            //retorna el certificado
            return $cert;
        } else {
            return null;
        }
    }    

    // FUNCION PARA COMPLETAR PENDIENTE Y PASARLO A ESTADO CERTIFICADO (REVISAR)
    public static function certificarGlpPendiente(Taller $taller, Servicio $servicio, Material $hoja, vehiculo $vehiculo, User $inspector, $precio)
    {
        $cert = Certificacion::create([
            "idVehiculo" => $vehiculo->id,
            "idTaller" => $taller->id,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio->id,
            "estado" => 1,
            "precio" => $precio,
            "pagado" => 0,
        ]);
        if ($cert) {
            //cambia el estado de la hoja a consumido
            $hoja->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            //crea y guarda el servicio y material usado en esta certificacion
            $servM = ServicioMaterial::create([
                "idMaterial" => $hoja->id,
                "idCertificacion" => $cert->id
            ]);
            //retorna el certificado
            return $cert;
        } else {
            return null;
        }
    }

    // FUNCION PARA CERTIFICAR CONVERSION GNV CON CHIP
    public static function certificarGnvConChip(Taller $taller, Servicio $servicio, Material $hoja, vehiculo $vehiculo, User $inspector, Material $chip, $externoValue, $fechaCertificado)
    {
        //Condicion para jalar el precio de la tabla servicios o precios_inspector
        if ($externoValue == 0) {
            $precio = $servicio->precio;
        } elseif ($externoValue == 1) {
            $precio = PrecioInspector::where([['idServicio', $servicio->TipoServicio->id], ['idUsers', $inspector->id]])->first();
            $precio = $precio ? $precio->precio : 0;
        }

        $datosSerie = self::obtenerSiguienteCorrelativo($servicio->id, $fechaCertificado);
        
        $cert = Certificacion::create([
            "idVehiculo" => $vehiculo->id,
            "idTaller" => $taller->id,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio->id,
            "estado" => 1,
            "precio" => $precio,
            "pagado" => 0,
            "externo" => $externoValue,
            "fechaCertificado" => $fechaCertificado,
            "numSerie"         => $datosSerie['numSerie'],
            "anioSerie"        => $datosSerie['anioSerie'],
        ]);
        if ($cert) {
            //cambia el estado de la hoja a consumido
            $hoja->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            $chip->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);

            //dd($chip);
            //crea y guarda el servicio y material usado en esta certificacion
            $servM = ServicioMaterial::create([
                "idMaterial" => $hoja->id,
                "idCertificacion" => $cert->id
            ]);
            $servM2 = ServicioMaterial::create([
                "idMaterial" => $chip->id,
                "idCertificacion" => $cert->id
            ]);
            //retorna el certificado
            return $cert;
        } else {
            return null;
        }
    }
    

    // FUNCION PARA DUPLICAR CERTIFICADOS GNV
    public static function duplicarCertificadoGnv(Duplicado $duplicado, Taller $taller, User $inspector, Servicio $servicio, Material $hoja, $externoValue, $fechaCertificado)
    {
        //Condicion para jalar el precio de la tabla servicios o precios_inspector
        if ($externoValue == 0) {
            $precio = $servicio->precio;
        } elseif ($externoValue == 1) {
            $precio = PrecioInspector::where([['idServicio', $servicio->TipoServicio->id], ['idUsers', $inspector->id]])->first();    
            $precio = $precio ? $precio->precio : 0;
        }

        $anterior = Certificacion::find($duplicado->idAnterior);

        $datosSerie = self::obtenerSiguienteCorrelativo($servicio->id, $fechaCertificado);

        $cert = Certificacion::create([
            "idVehiculo" => $anterior->Vehiculo->id,
            "idTaller" => $taller->id,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio->id,
            "estado" => 1,
            "precio" => $precio,
            "pagado" => 0,
            "idDuplicado" => $duplicado->id,
            "externo" => $externoValue,
            "fechaCertificado" => $fechaCertificado,
            "numSerie"         => $datosSerie['numSerie'],
            "anioSerie"        => $datosSerie['anioSerie'],
        ]);

        if ($cert) {
            //cambia el estado de la hoja a consumido
            $hoja->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            //crea y guarda el servicio y material usado en esta certificacion
            $servM = ServicioMaterial::create([
                "idMaterial" => $hoja->id,
                "idCertificacion" => $cert->id
            ]);
            //retorna el certificado
            return $cert;
        } else {
            return null;
        }
    }
    // FUNCION PARA DUPLICAR CERTIFICADOS EXTERNOS GNV
    public static function duplicarCertificadoExternoGnv(User $inspector, Vehiculo $vehiculo, Servicio $servicio, Taller $taller, Material $hoja, Duplicado $duplicado, $externoValue, $fechaCertificado)
    {
        //Condicion para jalar el precio de la tabla servicios o precios_inspector
        if ($externoValue == 0) {
            $precio = $servicio->precio;
        } elseif ($externoValue == 1) {
            $precio = PrecioInspector::where([['idServicio', $servicio->TipoServicio->id], ['idUsers', $inspector->id]])->first();
            $precio = $precio ? $precio->precio : 0;
        }

        $datosSerie = self::obtenerSiguienteCorrelativo($servicio->id, $fechaCertificado);

        $cert = Certificacion::create([
            "idVehiculo" => $vehiculo->id,
            "idTaller" => $taller->id,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio->id,
            "estado" => 1,
            "precio" => $precio,
            "pagado" => 0,
            "idDuplicado" => $duplicado->id,
            "externo" => $externoValue,
            "fechaCertificado" => $fechaCertificado,
            "numSerie"         => $datosSerie['numSerie'],
            "anioSerie"        => $datosSerie['anioSerie'],
        ]);
        if ($cert) {
            //cambia el estado de la hoja a consumido
            $hoja->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            //crea y guarda el servicio y material usado en esta certificacion
            $servM = ServicioMaterial::create([
                "idMaterial" => $hoja->id,
                "idCertificacion" => $cert->id
            ]);
            //retorna el certificado
            return $cert;
        } else {
            return null;
        }
    }


    // PARA GLP NO HAY DUPLICADOS (REVISAR)
    public static function duplicarCertificadoGlp(Duplicado $duplicado, Taller $taller, User $inspector, Servicio $servicio, Material $hoja, $externoValue)
    {

        //Condicion para jalar el precio de la tabla servicios o precios_inspector
        if ($externoValue == 0) {
            $precio = $servicio->precio;
        } elseif ($externoValue == 1) {
            $precio = PrecioInspector::where([
                ['idServicio', $servicio->TipoServicio->id],
                ['idUsers', $inspector->id]
            ])->first();
    
            if ($precio) {
                $precio = $precio->precio;
            } else {
                $precio = 0;
            }
        }

        $anterior = Certificacion::find($duplicado->idAnterior);
        $cert = Certificacion::create([
            "idVehiculo" => $anterior->Vehiculo->id,
            "idTaller" => $taller->id,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio->id,
            "estado" => 1,
            "precio" => $servicio->precio,
            "pagado" => 0,
            "idDuplicado" => $duplicado->id,
            "idTallerAuto" => $anterior->idTallerAuto, //Para taller autorizado
            "externo" => $externoValue, //agregamos el nuevo campo externo
        ]);

        if ($cert) {
            //cambia el estado de la hoja a consumido
            $hoja->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            //crea y guarda el servicio y material usado en esta certificacion
            $servM = ServicioMaterial::create([
                "idMaterial" => $hoja->id,
                "idCertificacion" => $cert->id
            ]);
            //retorna el certificado
            return $cert;
        } else {
            return null;
        }
    }
    // PARA GLP NO HAY DUPLICADOS (REVISAR)
    public static function duplicarCertificadoExternoGlp(User $inspector, Vehiculo $vehiculo, Servicio $servicio, Taller $taller, Material $hoja, Duplicado $duplicado, $externoValue)
    {
        //Condicion para jalar el precio de la tabla servicios o precios_inspector
        if ($externoValue == 0) {
            $precio = $servicio->precio;
        } elseif ($externoValue == 1) {
            $precio = PrecioInspector::where([
                ['idServicio', $servicio->TipoServicio->id],
                ['idUsers', $inspector->id]
            ])->first();
    
            if ($precio) {
                $precio = $precio->precio;
            } else {
                $precio = 0;
            }
        }

        $cert = Certificacion::create([
            "idVehiculo" => $vehiculo->id,
            "idTaller" => $taller->id,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio->id,
            "estado" => 1,
            "precio" => $precio,
            "pagado" => 0,
            "idDuplicado" => $duplicado->id,
            //"idTallerAuto" => $tallerAuto->id, //Para taller autorizado
            "externo" => $externoValue, //agregamos el nuevo campo externo
        ]);
        if ($cert) {
            //cambia el estado de la hoja a consumido
            $hoja->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            //crea y guarda el servicio y material usado en esta certificacion
            $servM = ServicioMaterial::create([
                "idMaterial" => $hoja->id,
                "idCertificacion" => $cert->id
            ]);
            //retorna el certificado
            return $cert;
        } else {
            return null;
        }
    }

    // FUNCION PARA CERTIFICAR CONVERSION OVERHUL GNV CON CHIP
    public static function certificarGnvOverhul(Taller $taller, Servicio $servicio, Material $hoja, vehiculo $vehiculo, User $inspector, Material $chip, $externoValue, $fechaCertificado)
    {
        // Condición para asignar el precio según el valor de $externoValue
        $precio = $externoValue == 0 ? $servicio->precio
            : PrecioInspector::where([['idServicio', $servicio->TipoServicio->id], ['idUsers', $inspector->id]])->value('precio') ?? 0;

        $datosSerie = self::obtenerSiguienteCorrelativo($servicio->id, $fechaCertificado);

        $cert = Certificacion::create([
            "idVehiculo" => $vehiculo->id,
            "idTaller" => $taller->id,
            "idInspector" => $inspector->id,
            "idServicio" => $servicio->id,
            "estado" => 1,
            "precio" => $precio,
            "pagado" => 0,
            "externo" => $externoValue,
            "fechaCertificado" => $fechaCertificado,
            "numSerie"         => $datosSerie['numSerie'],
            "anioSerie"        => $datosSerie['anioSerie'],
        ]);
        
        if ($cert) {
            //cambia el estado de la hoja a consumido
            $hoja->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            $chip->update(["estado" => 4, "ubicacion" => "En poder del cliente"]);
            //crea y guarda el servicio y material usado en esta certificacion
            $servM = ServicioMaterial::create([
                "idMaterial" => $hoja->id,
                "idCertificacion" => $cert->id
            ]);
            $servM2 = ServicioMaterial::create([
                "idMaterial" => $chip->id,
                "idCertificacion" => $cert->id
            ]);
            //retorna el certificado
            return $cert;
        } else {
            return null;
        }
    }
}
