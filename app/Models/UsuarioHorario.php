<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioHorario extends Model
{
    use HasFactory;

    protected $table = 'usuario_horarios';

    protected $fillable = ['user_id', 'horario_id', 'fecha_inicio', 'fecha_fin', 'activo'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }
}
