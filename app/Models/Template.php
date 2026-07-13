<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'template';

    protected $fillable = [
        'name',
        'description',
        'thumbnail',
        'content_html',
        'styles_css',
        'membresia',
    ];
}
