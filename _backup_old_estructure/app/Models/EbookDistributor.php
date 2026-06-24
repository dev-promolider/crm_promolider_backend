<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EbookDistributor extends Model
{
    use HasFactory;
    protected $table = 'ebook_distributor';
    protected $fillable = [
        'user_id',
        'ebook_id',
        'code',
        'expires_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function ebook()
    {
        return $this->belongsTo(Ebook::class);
    }
}
