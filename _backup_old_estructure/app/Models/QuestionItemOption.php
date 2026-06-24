<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionItemOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_item_id',
        'label',
        'text',
        'is_correct',
        'position',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'position' => 'integer',
    ];

    public function question()
    {
        return $this->belongsTo(QuestionItem::class, 'question_item_id');
    }
}
