<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int                 $id
 * @property string              $title
 * @property string|null         $description
 * @property string|null         $image_path
 * @property string|null         $url
 * @property string              $status       active|inactive
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @property-read string|null $image_url
 *
 * @method static Builder|static active()
 */
class News extends Model
{
    use HasFactory;

    /* -----------------------------------------------------------------
     |  Atributos asignables
     * ----------------------------------------------------------------- */
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'url',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    protected $appends = [
        'image_url',
    ];

    /* -----------------------------------------------------------------
     |  Scopes
     * ----------------------------------------------------------------- */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /* -----------------------------------------------------------------
     |  Accessors
     * ----------------------------------------------------------------- */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? asset('storage/' . ltrim($this->image_path, '/'))
            : null;
    }
}
