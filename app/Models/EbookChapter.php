<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbookChapter extends Model
{
    protected $table = 'ebook_chapters';

    protected $fillable = [
        'ebook_id',
        'title',
        'content',
        'pages',
    ];

    public function ebook()
    {
        return $this->belongsTo(Ebook::class);
    }
}
