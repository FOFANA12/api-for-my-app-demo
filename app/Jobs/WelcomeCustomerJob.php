<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\User;
use App\Notifications\WelcomeCustomerNotification;
use App\Notifications\WelcomeUserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class WelcomeCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $customer_id;
    private $passwd;
    public function __construct($customer_id, $passwd)
    {
        $this->customer_id = $customer_id;
        $this->passwd = $passwd;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $customer = Customer::where('id',$this->customer_id)->first();
        if($customer){
            $customer->notify(new WelcomeCustomerNotification($this->passwd));
        }
    }
}
