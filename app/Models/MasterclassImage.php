<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterclassImage extends Model
{
    protected $table = 'masterclass_images';

    protected $fillable = [
        'masterclass_id',
        'image',
        'url',
    ];

    public function masterclass()
    {
        return $this->belongsTo(Masterclass::class);
    }
}
