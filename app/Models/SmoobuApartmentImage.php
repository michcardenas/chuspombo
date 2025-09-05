<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmoobuApartmentImage extends Model
{
    protected $fillable = [
        'apartment_id',
        'path',
        'alt_text',
        'sort_order',
        'is_active',
    ];

    public function apartment()
    {
        return $this->belongsTo(SmoobuApartmentMeta::class, 'apartment_id', 'apartment_id');
    }

    // Helper opcional para obtener URL absoluta (si guardas 'storage/...'):
    public function getUrlAttribute(): string
    {
        return asset($this->path);
    }
}
