<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preferences extends Model
{
    use HasFactory;

    protected $table = 'preferences';

    protected $fillable = [
        'user_id',
        'categories_id',
    ];

    public $timestamps = true;

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'categories_id');
    }
}
