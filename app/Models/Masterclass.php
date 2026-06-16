<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Masterclass extends Model
{
    use HasFactory;
    protected $table = 'masterclasses';
    protected $fillable = [
        'user_id',
        'id_categories',
        'title',
        'description',
        'objectives',
        'date',
        'hour',
        'duration',
        'email_contact',
        'phone_contact',
        'status',
        'meeting_link',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'id_categories');
    }
    public function documents(): HasMany
    {
        return $this->hasMany(MasterclassDocument::class, 'masterclass_id');
    }
    public function images(): HasMany
    {
        return $this->hasMany(MasterclassImage::class, 'masterclass_id');
    }
    public function distributors(): HasMany
    {
        return $this->hasMany(MasterclassDistributor::class, 'masterclass_id');
    }

}
