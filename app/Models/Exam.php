<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $table = 'exam';

    protected $fillable = [
        'course_id',
        'productor_id',
        'module_id',
        'lesson_id',
        'title',
        'time',
        'max_score',
        'min_passing_score',
        'status',
    ];

    protected $casts = [
        'time' => 'integer',
        'max_score' => 'integer',
        'min_passing_score' => 'integer',
        'status' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(ExamQuestion::class, 'exam_id');
    }

    public function userHeaders()
    {
        return $this->hasMany(UserExamHeader::class, 'exam_id');
    }
}
