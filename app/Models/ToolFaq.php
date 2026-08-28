<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToolFaq extends Model
{
    protected $table = "tool_faqs";

    protected $fillable = [
        "tool_type",
        "tool_id",
        "question",
        "answer",
        "order",
    ];
}
