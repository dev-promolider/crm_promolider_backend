<?php namespace App\Services;

use App\Models\ClassroomPointDetail;
use App\Models\UserClassroomPoint;
use App\Models\UserLevel;

class UserLevelService 
{
    protected $cachedPoints = null;
    protected $cachedLevel = null;
    protected $cachedNextLevel = null;
    protected $cachedPorcentaje = null;
    protected $cachedPointsDetail = null;

    public function myPoints()
    {
        if ($this->cachedPoints !== null) return $this->cachedPoints;
        $user_id = auth()->user()->id;
        $points = UserClassroomPoint::select('total_points')->where('id_user',$user_id)->first();
        $this->cachedPoints = is_null($points) ? 0 : $points->total_points;
        return $this->cachedPoints;
    }

    public function myPointsDetail()
    {
        if ($this->cachedPointsDetail !== null) return $this->cachedPointsDetail;
        $user_id = auth()->user()->id;
        $this->cachedPointsDetail = ClassroomPointDetail::where('id_user_classroom_points',$user_id)->orderBy('increment_points','desc')->limit(5)->get();
        return $this->cachedPointsDetail;
    }

    public function getLevel()
    {
        if ($this->cachedLevel !== null) return $this->cachedLevel;
        $total_point = $this->myPoints();
        $this->cachedLevel = UserLevel::whereBetween('experience_required',[0,$total_point])
                            ->orderBy('experience_required','desc')->first();
        return $this->cachedLevel;
    }

    public function nextLevel(){
        if ($this->cachedNextLevel !== null) return $this->cachedNextLevel;
        $level = $this->getLevel();
        $this->cachedNextLevel = UserLevel::where('experience_required','>',$level->experience_required)
                            ->orderBy('experience_required','asc')->first();
        return $this->cachedNextLevel;
    }

    public function porcentaje()
    {   
        if ($this->cachedPorcentaje !== null) return $this->cachedPorcentaje;
        $total_point = $this->myPoints();
        $nextLevel = $this->nextLevel();
        
        if (!$nextLevel || $nextLevel->experience_required == 0) {
            $this->cachedPorcentaje = 100;
        } else {
            $this->cachedPorcentaje = ($total_point / $nextLevel->experience_required) * 100;
        }

        return $this->cachedPorcentaje;
    }
}
