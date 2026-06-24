<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horarios';

    protected $fillable = ['nombre', 'descripcion', 'activo'];

    public function detalles()
    {
        return $this->hasMany(HorarioDetalle::class);
    }

    public function usuarios()
    {
        return $this->hasMany(UsuarioHorario::class);
    }
}
