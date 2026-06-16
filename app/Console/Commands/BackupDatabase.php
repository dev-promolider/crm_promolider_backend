<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';
  
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
        $path = storage_path() ."/app/backup/";

        if(!File::isDirectory($path)){
        File::makeDirectory($path, 0777, true, true);
        }   

        $filename = "backup-" . Carbon::now()->format('Y-m-d') . ".gz";
        $filename2 = "backup-" . Carbon::now()->format('Y-m-d') . ".sql";
  
        //$command = "mysqldump --user=" . env('DB_USERNAME') ." --password=" . env('DB_PASSWORD') . " --host=" . env('DB_HOST') . " " . env('DB_DATABASE') . "  | gzip > " . storage_path() . "/app/backup/" . $filename;
        $command = "mysqldump -u " . env('DB_USERNAME') ." -p" . env('DB_PASSWORD') . " " . env('DB_DATABASE') . "  > " . storage_path() . "/app/backup/" . $filename2;
        $returnVar = NULL;
        $output  = NULL;
        //echo($command);
        exec($command, $output, $returnVar);
    }
}
