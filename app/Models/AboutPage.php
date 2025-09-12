<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AboutPage extends Model
{
    use HasFactory;

    protected $table = 'about_pages';

    protected $fillable = [
        // HERO
        'hero_title', 'hero_subtitle', 'hero_cta_text', 'hero_cta_url', 'hero_image',

        // NUESTRA HISTORIA
        'story_title', 'story_subtitle', 'story_text',

        // EXPERIENCIA (3 tarjetas)
        'exp_title', 'exp_subtitle',
        'exp_card1_title', 'exp_card1_text',
        'exp_card2_title', 'exp_card2_text',
        'exp_card3_title', 'exp_card3_text',

        // POR QUÉ ELEGIRNOS (3 items)
        'why_title',
        'why_item1_title', 'why_item1_text',
        'why_item2_title', 'why_item2_text',
        'why_item3_title', 'why_item3_text',

        // CTA FINAL
        'cta_title', 'cta_text', 'cta_button_text', 'cta_button_url',
        'cta_phone_label', 'cta_phone_number', 'cta_background_image',

        // Estado
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /* ===========================
     |  Accesores de URLs de imagen
     |===========================*/
    public function getHeroImageUrlAttribute(): ?string
    {
        return $this->imageUrl($this->hero_image);
    }

    public function getCtaBackgroundImageUrlAttribute(): ?string
    {
        return $this->imageUrl($this->cta_background_image);
    }

    /* ===========================
     |  Scopes útiles
     |===========================*/
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    /* ===========================
     |  Helpers
     |===========================*/
    protected function imageUrl(?string $path): ?string
    {
        if (!$path) return null;
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }
        // Por convención guardas solo el nombre; servimos desde /public/images
        return asset('images/' . ltrim($path, '/'));
    }
}
