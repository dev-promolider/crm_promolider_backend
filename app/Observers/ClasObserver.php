<?php

namespace App\Observers;

use App\Models\Clas;
use Illuminate\Support\Facades\Storage;

class ClasObserver
{
    /**
     * Handle the Clas "created" event.
     *
     * @param  \App\Models\Clas  $clas
     * @return void
     */
    public function created(Clas $clas)
    {
        //
    }

    /**
     * Handle the Clas "updated" event.
     *
     * @param  \App\Models\Clas  $clas
     * @return void
     */
    public function updated(Clas $clas)
    {
        //
    }

    /**
     * Handle the Clas "deleted" event.
     *
     * @param  \App\Models\Clas  $clas
     * @return void
     */
    public function deleted(Clas $clas)
    {
        //
    }

    /**
     * Handle the Clas "deleting" event.
     *
     * @param  \App\Models\Clas  $clas
     * @return void
     */
    public function deleting(Clas $clas)
    {
        if (!empty($clas->video)) {
            Storage::delete($clas->video->path);
            $clas->video()->delete();
        }
    }
}
