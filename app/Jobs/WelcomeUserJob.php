<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\WelcomeUserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class WelcomeUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $user_id;
    private $passwd;
    public function __construct($user_id, $passwd)
    {
        $this->user_id = $user_id;
        $this->passwd = $passwd;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::where('id',$this->user_id)->first();
        if($user){
            $user->notify(new WelcomeUserNotification($this->passwd));
        }
    }
}
