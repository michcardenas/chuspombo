<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmoobuApartmentMeta extends Model
{
    protected $table = 'smoobu_apartment_meta';
    protected $primaryKey = 'apartment_id';
    public $incrementing = false;

    protected $fillable = [
        'apartment_id',
        'title',
        'slug',
        'description',
        'city',
        'country',
        'bedrooms',
        'bathrooms',
        'cover_image_path',
        'base_price_override',
        'calendar_verification',   // 👈 NUEVO
        'is_published',
        'sort_order',
        'extras',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'bathrooms' => 'float',
        'base_price_override' => 'float',
        'extras' => 'array',
    ];

    public function mainImage()
    {
        // Imagen activa con menor sort_order (cubre el caso sort_order=1)
        return $this->hasOne(SmoobuApartmentImage::class, 'apartment_id', 'apartment_id')
            ->where('is_active', true)
            ->oldestOfMany('sort_order'); // min(sort_order)
    }
}
