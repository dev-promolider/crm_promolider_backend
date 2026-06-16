<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EditTemplate;

class Template extends Model
{
    use HasFactory;

    protected $table = 'template';
    
    protected $fillable = ['name', 'description', 'thumbnail', 'content_html', 'styles_css', 'membresia'];

    public function editTemplates()
    {
        return $this->hasMany(EditTemplate::class);
    }
}
