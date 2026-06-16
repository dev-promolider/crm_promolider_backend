<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ebook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'price',
        'author',
        'pages',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'pages' => 'integer',
        'status' => 'integer',
    ];

    /**
     * Relación con el usuario propietario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la categoría
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relación con las imágenes del ebook
     */
    public function images()
    {
        return $this->hasMany(EbookImage::class);
    }

    /**
     * Relación con los documentos del ebook
     */
    public function documents()
    {
        return $this->hasMany(EbookDocument::class);
    }

    /**
     * Relación con los capítulos del ebook
     */
    public function chapters()
    {
        return $this->hasMany(EbookChapter::class);
    }

    /**
     * Accessor para el precio formateado
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2);
    }

    /**
     * Scope para ebooks activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope para ebooks de un usuario específico
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
        /**
     * Distribuidores
     */
    public function distributors(): HasMany
    {
        return $this->hasMany(EbookDistributor::class, 'ebook_id');
    }
}
