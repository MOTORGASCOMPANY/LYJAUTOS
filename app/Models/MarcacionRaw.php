<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarcacionRaw extends Model
{
    use HasFactory;

    protected $table = 'marcaciones_raw';

    protected $fillable = [
        'user_id', 'dni_usado', 'momento_marcado', 'tipo', 'ip_origen', 'metodo_verificacion'
    ];

    // Importante para que Laravel trate 'momento_marcado' como objeto Carbon
    protected $dates = ['momento_marcado'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
