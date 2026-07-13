<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EbookUser extends Model
{
    protected $table = 'ebook_users';

    protected $fillable = [
        'ebook_distributor_id',
        'name',
        'lastname',
        'email',
        'phone',
        'age',
        'nationality',
        'isParticipant',
    ];

    protected $casts = [
        'isParticipant' => 'boolean',
    ];

    public function distributor()
    {
        return $this->belongsTo(EbookDistributor::class, 'ebook_distributor_id');
    }
}
