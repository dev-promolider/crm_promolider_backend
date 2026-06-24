<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'question_category_id',
        'title',
        'body',
        'status',
        'difficulty',
        'time_limit',
        'is_active',
        'meta',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
        'time_limit' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(QuestionCategory::class, 'question_category_id');
    }

    public function options()
    {
        return $this->hasMany(QuestionItemOption::class)->orderBy('position');
    }
}
