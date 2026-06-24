<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class RemoveBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:removebackup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filename = "backup-" . Carbon::now()->subDays(2)->format('Y-m-d') . ".sql";
        $contents = storage_path().'/app/backup/'.$filename;
        

        if(is_file($contents)){
         unlink(storage_path('app/backup/'.$filename));
        }
        else
        {
            echo "File does not exist";
        }
        return 0;
    }
}
