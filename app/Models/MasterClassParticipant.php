<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterClassParticipant extends Model
{
    protected $table = 'master_class_participants';

    protected $fillable = [
        'master_class_id',
        'fullname',
        'email',
        'phone',
    ];

    public function masterClassVideo()
    {
        return $this->belongsTo(MasterClassVideo::class, 'master_class_id');
    }
}
