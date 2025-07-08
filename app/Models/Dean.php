<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dean extends Model
{
    // Si tu tabla se llama "deans", no necesitas $table.
    protected $fillable = [
        'image_url',   // URL o ruta dentro de storage
        'name',
        'year_start',
        'year_end',
        'is_active',
    ];

    // Casts para que Laravel devuelva boolean correctamente
    protected $casts = [
        'is_active' => 'boolean',
        'year_start' => 'integer',
        'year_end'   => 'integer',
    ];
}
