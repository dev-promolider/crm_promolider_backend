<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbookDocument extends Model
{
    protected $table = 'ebook_documents';

    protected $fillable = [
        'ebook_id',
        'document',
    ];

    public function ebook()
    {
        return $this->belongsTo(Ebook::class);
    }
}
