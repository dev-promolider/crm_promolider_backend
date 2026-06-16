<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EbookDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'ebook_id',
        'document',
    ];

    /**
     * Relación con el ebook
     */
    public function ebook()
    {
        return $this->belongsTo(Ebook::class);
    }

    /**
     * Accessor para la URL completa del documento
     */
    public function getDocumentUrlAttribute()
    {
        return asset($this->document);
    }
}
