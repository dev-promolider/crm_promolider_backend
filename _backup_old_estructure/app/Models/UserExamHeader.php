<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserExamHeader extends Model
{
    use HasFactory;

    protected $table = "user_exam_header";

    protected $fillable = [
        'user_id',
        'productor_id',
        'rate',
        'status',
        'condition',
    ];
}
