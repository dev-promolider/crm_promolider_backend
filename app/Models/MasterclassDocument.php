<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterclassDocument extends Model
{
    protected $table = 'masterclass_documents';

    protected $fillable = [
        'masterclass_id',
        'document',
        'url',
        'name',
    ];

    public function masterclass()
    {
        return $this->belongsTo(Masterclass::class);
    }
}
