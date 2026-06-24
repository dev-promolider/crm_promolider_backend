<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterclassImage extends Model
{
    use HasFactory;
    protected $table = 'masterclass_images';
    protected $fillable = [
        'masterclass_id',
        'image',
    ];
    public function masterclass(): BelongsTo
    {
        return $this->belongsTo(Masterclass::class);
    }
}
