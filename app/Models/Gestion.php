<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Miembro;

class Gestion extends Model
{
    protected $table = 'gestiones';
    protected $fillable = ['nombre', 'lema', 'inicio', 'fin', 'status'];

    protected $casts = [
        'inicio' => 'integer',
        'fin' => 'integer',
    ];

    public function miembros()
    {
        return $this->hasMany(Miembro::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactivas($query)
    {
        return $query->where('status', 'inactive');
    }
    // App\Models\Gestion.php
protected static function booted()
{
    static::deleting(function ($gestion) {
        // Eliminar todos los miembros relacionados
        $gestion->miembros()->each(function ($miembro) {
            if ($miembro->img && Storage::disk('public')->exists($miembro->img)) {
                Storage::disk('public')->delete($miembro->img);
            }
            $miembro->delete();
        });
    });
}
}
