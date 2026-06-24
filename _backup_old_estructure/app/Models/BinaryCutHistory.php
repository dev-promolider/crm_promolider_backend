<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BinaryCutHistory extends Model
{
    protected $fillable = [
        'user_id', 
        'rank_id', 
        'left_points', 
        'right_points', 
        'transferred_amount',
        'batch'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rank()
    {
        return $this->belongsTo(RankBonus::class);
    }
}
