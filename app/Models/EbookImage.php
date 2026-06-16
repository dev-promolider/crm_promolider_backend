<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EbookImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ebook_id',
        'image',
    ];

    /**
     * Relación con el ebook
     */
    public function ebook()
    {
        return $this->belongsTo(Ebook::class);
    }

    /**
     * Accessor para la URL completa de la imagen
     */
    public function getImageUrlAttribute()
    {
        return asset($this->image);
    }
}
