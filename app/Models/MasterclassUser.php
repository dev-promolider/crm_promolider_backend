<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterclassUser extends Model
{
    protected $table = 'masterclass_user';

    protected $fillable = [
        'masterclass_distributor_id',
        'name',
        'lastname',
        'email',
        'phone',
        'age',
        'nationality',
        'user_type',
        'isParticipant',
    ];

    public function masterclass()
    {
        return $this->belongsTo(Masterclass::class, 'masterclass_id');
    }

    public function distributor()
    {
        return $this->belongsTo(MasterclassDistributor::class, 'masterclass_distributor_id');
    }
}
