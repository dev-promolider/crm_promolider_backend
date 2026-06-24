<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Support\Str;

class EditTemplate extends Model
{
    use HasFactory;

    protected $table = 'edit_template'; // ✅ Asegúrate de que el nombre coincida con la tabla real

    protected $fillable = [
        'user_id',
        'template_id',
        'title',
        'slug',
        'content_html',
        'edited_fields',
        'status',
    ];

    protected $casts = [
        'edited_fields' => 'array', // ✅ Laravel trata 'array' como JSON automático
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Slug dinámico y URL pública
    |--------------------------------------------------------------------------
    */
    public function generateSlug(): string
    {
        $baseSlug = Str::slug($this->title);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    public function getPublicUrlAttribute(): string
    {
        return ($this->status === 'published' && $this->slug)
            ? url("/pages/{$this->slug}")
            : '';
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /*
    |--------------------------------------------------------------------------
    | Event Hooks (Boot)
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->status === 'published' && empty($model->slug)) {
                $model->slug = $model->generateSlug();
            }
        });

        static::updating(function ($model) {
            if ($model->status === 'published' && empty($model->slug)) {
                $model->slug = $model->generateSlug();
            }
        });
    }
}
