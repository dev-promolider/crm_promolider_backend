<?php

namespace App\Models;

use App\Helpers\ParseUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;
    
    public function getIconAttribute($value)
    {
        return  ParseUrl::contacAtrrS3badges($value);
    }
}
