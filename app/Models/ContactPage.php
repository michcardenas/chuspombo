<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactPage extends Model
{
    protected $table = 'contact_pages';

    protected $fillable = [
        // Textos principales
        'h1', 'h2', 'intro_text', 'side_text',

        // Datos de contacto
        'email_primary', 'email_secondary',
        'phone_primary', 'phone_secondary',
        'whatsapp', 'website',

        // Dirección
        'address_line1', 'address_line2', 'city', 'region', 'postal_code', 'country',

        // Mapa
        'map_embed_url', 'latitude', 'longitude',

        // Redes
        'facebook_url', 'instagram_url', 'twitter_url', 'tiktok_url', 'youtube_url', 'linkedin_url',

        // Imágenes / media
        'hero_image', 'banner_image',

        // Formulario
        'form_recipient', 'form_cc', 'success_message', 'legal_checkbox_label', 'legal_link_url',

        // Horarios
        'business_hours',

        // Estado
        'is_active',
    ];

    protected $casts = [
        'business_hours' => 'array',
        'is_active'      => 'boolean',
        'latitude'       => 'float',
        'longitude'      => 'float',
    ];

    protected $appends = [
        'hero_image_url',
        'banner_image_url',
    ];

    /* ----------------------------
     |  Scopes
     * ---------------------------- */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Devuelve (o crea) el único registro de ContactPage que usarás como “singleton”.
     */
    public static function singleton(): self
    {
        return static::firstOrCreate([]);
    }

    /* ----------------------------
     |  Accessors (URLs absolutas)
     * ---------------------------- */
    public function getHeroImageUrlAttribute(): ?string
    {
        $p = $this->hero_image;
        if (!$p) return null;

        // Si ya es absoluta, se respeta
        if (preg_match('/^https?:\/\//i', $p)) return $p;

        return asset('images/' . ltrim($p, '/'));
    }

    public function getBannerImageUrlAttribute(): ?string
    {
        $p = $this->banner_image;
        if (!$p) return null;

        if (preg_match('/^https?:\/\//i', $p)) return $p;

        return asset('images/' . ltrim($p, '/'));
    }
}
