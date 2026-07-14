<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseObservation extends Model
{
    use HasFactory;

    protected $table = 'course_observations';

    protected $fillable = [
        'id_class',
        'id_analyst',
        'id_productor',
        'observation',
        'id_courses',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function class()
    {
        return $this->belongsTo(Clas::class, 'id_class');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_courses');
    }

    public function analyst()
    {
        return $this->belongsTo(User::class, 'id_analyst');
    }

    public function productor()
    {
        return $this->belongsTo(User::class, 'id_productor');
    }
}
