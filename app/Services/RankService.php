<?php

namespace App\Services;

use App\Helpers\ParseUrl;
use App\Models\RankBinary;
use App\Models\RankBonus;

class RankService
{
    protected $cachedMyRank = null;

    public function myRank()
    {
        if ($this->cachedMyRank !== null) return $this->cachedMyRank;

        $user_id = auth()->user()->id;
        $rank = RankBinary::join('rank_bonus', 'rank_bonus.id', '=', 'rank_id')
            ->where('user_id', $user_id)->orderBy('rank_binary.created_at', 'desc')
            ->select('rank_bonus.*')
            ->first();

        if (is_null($rank)) {
            $rank = RankBonus::select('rank_bonus.*')
                ->first();
            $rank->icon = RankBonus::getPhotoAttribute($rank->icon);
        }else{

            $rank->icon = RankBonus::getPhotoAttribute($rank->icon);

        }

        $this->cachedMyRank = $rank;
        return $this->cachedMyRank;
    }
}