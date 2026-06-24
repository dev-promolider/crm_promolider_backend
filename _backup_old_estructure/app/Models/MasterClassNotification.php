<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterClassNotification extends Model
{
    use HasFactory;

    protected $table = 'master_class_notification'; // 👈 nombre de la tabla

    protected $fillable = [
        'transmitter',
        'receiver',
        'title',
        'body',
        'url',
        'icon',
        'seen',
    ];
}
