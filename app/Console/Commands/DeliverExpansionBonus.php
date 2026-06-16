<?php

namespace App\Console\Commands;

use App\Models\AccountType;
use App\Models\ExpansionBonus;
use App\Models\Option;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletMovements;
use Illuminate\Console\Command;

class DeliverExpansionBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deliver:expansionBonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'deliver expansion bonus to users qualified and active';

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
        $users = User::qualifiedsAndActive();
        $last_expansion_bonus = Option::where('description', 'last_expansion_deliver')->pluck('value')->first();
        $last_expansion_bonus = strtotime($last_expansion_bonus);
        foreach ($users as $user) {
            $wallet_id = Wallet::where('user_id', $user->id)->pluck('id')->first();
            $count_school = User::where('id_referrer_sponsor', $user->id)
                ->whereDate('created_at', '<', $last_expansion_bonus)
                ->where('id_account_type', 2)
                ->count();
            $count_academy = User::where('id_referrer_sponsor', $user->id)
                ->whereDate('created_at', '<', $last_expansion_bonus)
                ->where('id_account_type', 3)
                ->count();
            $count_university = User::where('id_referrer_sponsor', $user->id)
                ->whereDate('created_at', '<', $last_expansion_bonus)
                ->where('id_account_type', 4)
                ->count();
            if($count_school > 3){
                $account_type_id = 2;
                $users_qty = $count_school >= 7 ? 7 : $count_school;
                $this->deliverExpansionBonus($wallet_id, $users_qty, $account_type_id);
            }
            if($count_academy > 3){
                $account_type_id = 3;
                $users_qty = $count_academy >= 7 ? 7 : $count_academy;
                $this->deliverExpansionBonus($wallet_id, $users_qty, $account_type_id);
            }
            if($count_university > 3){
                $account_type_id = 4;
                $users_qty = $count_university >= 7 ? 7 : $count_university;
                $this->deliverExpansionBonus($wallet_id, $users_qty, $account_type_id);
            }
        }  
        $new_date = Option::where('description', 'last_expansion_deliver')->first();
        $new_date->value = now();
        $new_date->update();
    }

    public function deliverExpansionBonus($wallet_id, $users_qty, $account_type_id){
        $bonus_percentage = ExpansionBonus::where('id_account_type', $account_type_id)
            ->where('name', $users_qty.'-users')
            ->pluck('value')->first();
        $membership_price = AccountType::where('id', $account_type_id)->pluck('price')->first();
        $bonus = new WalletMovements();
        $bonus->wallet_id = $wallet_id;
        $bonus->amount = ($membership_price * $bonus_percentage / 100) * $users_qty;
        $bonus->type = 1;
        $bonus->status = 1;
        $bonus->reason = "Bono de expansión";
        $bonus->bonus_type_id = 6;
        $bonus->batch = 0;
        $bonus->save();
    }

}
