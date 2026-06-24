<?php

namespace App\Console\Commands;

use App\Models\AccountTypeDetail;
use App\Models\AccountTypeDetailHistory;
use Illuminate\Console\Command;

class ValidateMembershipExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'membership:expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'valid if the date of the membership expired to change its status';

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
        $date = date("Y-m-d H:i:s");
        $accountTypeDetails = AccountTypeDetail::where('status', true)->where('expiration_date','<', $date)->get();
        foreach ($accountTypeDetails as $accountTypeDetail) {
            $accountTypeDetail->status = 0;
            if($accountTypeDetail->save()){
                
                $accountTypeDetailHistory = AccountTypeDetailHistory::where('account_type_detail_id',$accountTypeDetail->id)
                    ->where('status',true)->get()->first();
                    if($accountTypeDetailHistory){
                        $accountTypeDetailHistory->status = false;
                        $accountTypeDetailHistory->save();
                    }
            }
        }
    }
}
