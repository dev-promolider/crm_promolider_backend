<?php

namespace App\Observers;

use App\Models\Module;
use Illuminate\Support\Facades\Storage;

class ModuleObserver
{
    /**
     * Handle the Module "created" event.
     *
     * @param  \App\Models\Module  $module
     * @return void
     */
    public function created(Module $module)
    {
        //
    }

    /**
     * Handle the Module "updated" event.
     *
     * @param  \App\Models\Module  $module
     * @return void
     */
    public function updated(Module $module)
    {
        //
    }

    /**
     * Handle the Module "deleted" event.
     *
     * @param  \App\Models\Module  $module
     * @return void
     */
    public function deleted(Module $module)
    {
    } 
    /**
     * Handle the Module "deleting" event.
     *
     * @param  \App\Models\Module  $module
     * @return void
     */
    public function deleting(Module $module)
    {
        foreach ($module->lessons as $lesson) { // $module->lessons()->delete();
            $lesson->delete();
            // if (!empty($lesson->video)) {
            //     Storage::delete($lesson->video->path);
            //     $lesson->video()->delete();
            //     $lesson->delete();
            // }
        } 
        
    }

}
