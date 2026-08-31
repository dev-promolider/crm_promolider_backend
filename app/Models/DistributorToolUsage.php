<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorToolUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'usageable_id',
        'usageable_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function usageable()
    {
        return $this->morphTo();
    }
}
