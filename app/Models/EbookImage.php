<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbookImage extends Model
{
    protected $table = 'ebook_images';

    protected $fillable = [
        'ebook_id',
        'image',
    ];

    public function ebook()
    {
        return $this->belongsTo(Ebook::class);
    }
}
