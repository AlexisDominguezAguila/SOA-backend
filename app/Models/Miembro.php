<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Miembro extends Model
{
    protected $fillable = ['nombre', 'cargo', 'img', 'is_active', 'gestion_id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['img_url'];

    public function gestion()
    {
        return $this->belongsTo(Gestion::class);
    }

    public function getImgUrlAttribute()
    {
        return $this->img ? asset('storage/' . $this->img) : null;
    }

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }
}
