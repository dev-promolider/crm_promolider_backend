<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EbookUser extends Model
{
    use HasFactory;

    protected $table = 'ebook_users';

    protected $fillable = [
        'ebook_distributor_id',
        'name',
        'lastname',
        'email',
        'phone',
        'nationality',
        'age',
        'isParticipant',
        'observation',
    ];

    // Relación con el distribuidor
    public function distributor()
    {
        return $this->belongsTo(EbookDistributor::class, 'ebook_distributor_id');
    }
}
