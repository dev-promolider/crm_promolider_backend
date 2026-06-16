<?php
namespace App\Models\Traits;

use App\Models\Point;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Pointable
{
     /**
     * Get all of the points for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function points(): HasMany
    {
        return $this->hasMany(Point::class,'sponsor_id','id');
    

    }
    
    public function getLeftPointsAttribute()
    {
        return $this->points()->where('side',0)->where('status',1)->sum('points');
    }

    public function getRightPointsAttribute()
    {
        return $this->points()->where('side',1)->where('status',1)->sum('points');
    }
}