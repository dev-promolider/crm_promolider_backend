<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterclassDocument extends Model
{
    use HasFactory;
    protected $table = 'masterclass_documents';
    protected $fillable = [
        'masterclass_id',
        'document',
    ];
    public function masterclass(): BelongsTo
    {
        return $this->belongsTo(Masterclass::class);
    }
}
