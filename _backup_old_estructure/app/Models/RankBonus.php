<?php

namespace App\Models;
use App\Helpers\ParseUrl;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RankBonus extends Model
{
    use HasFactory;
    protected $table = 'rank_bonus';

    protected $fillable = [
        'name',
        'vol_min',
        'pack_max',
        'active_direct',
        'max_pay',
        'monthly_bonus'
    ];

    public static function getPhotoAttribute($value)
    {
        return  ParseUrl::contacAtrrS3($value);
    }
}