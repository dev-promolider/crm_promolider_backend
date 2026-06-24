<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterclassUser extends Model
{
    use HasFactory;
    protected $table = 'masterclass_user';
    protected $fillable = [
        'masterclass_distributor_id',
        'name',
        'lastname',
        'email',
        'phone',
        'nationality',
        'age',
        'user_type',
    ];
}
