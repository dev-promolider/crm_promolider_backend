<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserExamHeader extends Model
{
    use HasFactory;

    protected $table = 'user_exam_header';

    protected $fillable = [
        'user_id',
        'productor_id',
        'exam_id',
        'rate',
        'status',
        'condition',
    ];

    protected $casts = [
        'rate' => 'float',
        'status' => 'boolean',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
