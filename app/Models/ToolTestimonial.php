<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ToolTestimonial extends Model
{
    protected $table = "tool_testimonials";

    protected $fillable = [
        "tool_type",
        "tool_id",
        "author_name",
        "content",
        "order",
    ];
}
