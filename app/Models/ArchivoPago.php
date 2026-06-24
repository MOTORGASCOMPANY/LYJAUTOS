<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchivoPago extends Model
{
    use HasFactory;

    protected $table = 'archivos_pagos';

    protected $fillable = [
        'archivoable_id',
        'archivoable_type',
        'tipo', // boleta, comprobante default comprobante
        'estado', // 'generado', 'firmado' default 'generado'
        'nombre',
        'ruta',
        'extension',
    ];

    public function archivoable()
    {
        return $this->morphTo();
    }
}
