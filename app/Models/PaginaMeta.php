<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaginaMeta extends Model
{
    use HasFactory;

    protected $table = 'pagina_metas'; // explícito por claridad

    protected $fillable = [
        'pagina_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'robots',
        'author',
        'language',
        'viewport',
        'charset'
    ];

    /**
     * Relación con la página principal
     */
    public function pagina()
    {
        return $this->belongsTo(Pagina::class);
    }
}
