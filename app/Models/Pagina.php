<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pagina extends Model
{
    use HasFactory;

    protected $table = 'paginas';

    protected $fillable = [
        'h1',
        'h2_1',
        'h2_propiedades',
        'p_propiedades',
        'h2_hostella',
        'p_hostella',
        'p_lugar_favorito',
        'h2_confiar',
        'p_confiar',

        // Tarjetas sección 1
        'card1_title_1',
        'card1_content_1',
        'card1_title_2',
        'card1_content_2',
        'card1_title_3',
        'card1_content_3',
        'card1_image_1',
        'card1_image_2',
        'card1_image_3',

        // Tarjetas sección 2
        'card2_title_1',
        'card2_content_1',
        'card2_title_2',
        'card2_content_2',
        'card2_title_3',
        'card2_content_3',

        // Nuevas tarjetas sección 2 (4 a 7)
        'card2_title_4',
        'card2_content_4',
        'card2_image_4',
        'card2_title_5',
        'card2_content_5',
        'card2_image_5',
        'card2_title_6',
        'card2_content_6',
        'card2_image_6',
        'card2_title_7',
        'card2_content_7',
        'card2_image_7',

        // Información general
        'facebook',
        'instagram',
        'whatsapp',
        'logo',
    ];

    /**
     * Relación uno a uno con los metadatos SEO
     */
    public function meta()
    {
        return $this->hasOne(PaginaMeta::class);
    }
}
