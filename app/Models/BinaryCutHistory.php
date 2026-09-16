<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BinaryCutHistory extends Model
{
    protected $table = 'binary_cut_histories';

    protected $fillable = [
        'user_id',
        'rank_id',
        'account_type_id',
        'left_points',
        'right_points',
        'pay_percentage',
        'calculated_amount',
        'capped',
        'transferred_amount',
        'carryover_points',
        'carryover_side',
        'batch'
    ];

    protected $casts = [
        'capped' => 'boolean',
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
