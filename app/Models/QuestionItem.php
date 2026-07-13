<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'question_items';

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
        'time_limit' => 'integer',
        'meta' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(QuestionCategory::class, 'question_category_id');
    }

    public function options()
    {
        return $this->hasMany(QuestionItemOption::class, 'question_item_id')->orderBy('position');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
