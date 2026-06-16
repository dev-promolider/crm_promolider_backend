<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterclassDistributor extends Model
{
    use HasFactory;
    protected $table = 'masterclass_distributor';
    protected $fillable = [
        'user_id',
        'masterclass_id',
        'code',
        'expires_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
