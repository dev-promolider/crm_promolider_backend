<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Masterclass extends Model
{
    protected $table = 'masterclasses';

    protected $fillable = [
        'user_id',
        'producer_id',
        'id_categories',
        'title',
        'description',
        'objectives',
        'date',
        'hour',
        'duration',
        'email_contact',
        'phone_contact',
        'status',
        'meeting_link',
        'marketplace_listed',
        'is_private',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'id_categories');
    }

    public function images()
    {
        return $this->hasMany(MasterclassImage::class, 'masterclass_id');
    }

    public function documents()
    {
        return $this->hasMany(MasterclassDocument::class, 'masterclass_id');
    }
}
