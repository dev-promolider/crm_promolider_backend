<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $table = 'modules';

    protected $fillable = [
        'id_courses',
        'name',
        'order',
        'status',
    ];

    protected $casts = [
        'order' => 'integer',
        'status' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_courses');
    }

    public function classes()
    {
        return $this->hasMany(Clas::class, 'id_modules');
    }
}
