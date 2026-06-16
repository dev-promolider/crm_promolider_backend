<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;
    protected $table = 'options';
    protected $fillable = [
        "id",
        "description",
        "value",
    ];

    public function scopeLastBatch($query){
        return $query->where('description', 'batch')->first('value');
    }
}
