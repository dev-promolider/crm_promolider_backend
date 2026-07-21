<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\MLM\BinaryTreeService;

class RebuildBinaryTreeCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(BinaryTreeService $treeService)
    {
        // Reconstruye el árbol completo en memoria y lo guarda en Redis.
        // Esto se ejecuta en background (queue) para no retrasar el registro de usuarios.
        $treeService->buildTreeAndCache();
    }
}
