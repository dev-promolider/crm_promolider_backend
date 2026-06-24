<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCertificate extends Model
{
    use HasFactory;
    protected $table = 'user_certificates';
    protected $fillable = [
        "id",
        "certificate",
        "id_user",
        "id_course",
        "is_paid",
    ];
}
