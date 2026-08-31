<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ebook extends Model
{
    protected $table = 'ebooks';

    protected $fillable = [
        'user_id',
        'course_id',
        'producer_id',
        'category_id',
        'title',
        'description',
        'price',
        'author',
        'pages',
        'status',
        'marketplace_listed',
        'is_private',
        'landing_banner',
    ];

    public function usages()
    {
        return $this->morphMany(DistributorToolUsage::class, 'usageable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function course()
    {
        return $this->belongsTo(\App\Models\Infoproduct\Infoproduct::class, 'course_id');
    }

    public function images()
    {
        return $this->hasMany(EbookImage::class, 'ebook_id');
    }

    public function chapters()
    {
        return $this->hasMany(EbookChapter::class, 'ebook_id');
    }
}
