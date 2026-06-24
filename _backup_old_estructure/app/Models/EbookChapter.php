<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EbookChapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'ebook_id',
        'title',
        'content',
        'pages',
    ];

    protected $casts = [
        'pages' => 'integer',
    ];

    /**
     * Relación con el ebook
     */
    public function ebook()
    {
        return $this->belongsTo(Ebook::class);
    }

    /**
     * Accessor para el resumen del contenido
     */
    public function getContentSummaryAttribute()
    {
        return substr($this->content, 0, 100) . '...';
    }
}
