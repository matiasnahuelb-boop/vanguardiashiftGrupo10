<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Turno extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cliente_id', 'profesional_id', 'fecha_hora', 'motivo', 'estado'
    ];

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function profesional()
    {
        return $this->belongsTo(User::class, 'profesional_id');
    }
}
