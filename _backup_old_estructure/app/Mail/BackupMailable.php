<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;


class BackupMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $subject = 'Backup de la BD';
    /**
     * Create a new message instance.
     *
     * @return void
     */

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $filename = "backup-" . Carbon::now()->format('Y-m-d') . ".sql";
        $contents = storage_path().'/app/backup/'.$filename;
        return $this->view('welcome')
        ->subject('Backup de la BD')
        ->attach($contents);
    }
}
