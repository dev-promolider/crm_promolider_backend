<?php

namespace App\Models;

use App\Helpers\ParseUrl;
use Illuminate\Database\Eloquent\Model;

class MasterClassVideo extends Model
{
    protected $table = 'master_class_video';


    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function getBannerAttribute($value)
    {
        return  ParseUrl::contacAtrrS3($value);
    }
}
