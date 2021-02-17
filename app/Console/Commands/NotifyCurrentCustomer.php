<?php

namespace App\Console\Commands;

use App\Events\NotifyCustomerMessage;
use App\Models\Customer;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyCurrentCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:customer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify current customer';

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
     * @return mixed
     */
    public function handle()
    {
        /*$customers = Customer::all();
        foreach ($customers as $customer){
            $customer->notificationMessages()->create([
                'type' => 'success',
                'data' => json_encode(
                    [
                        'from' => 'HADES Consulting',
                        'subject' => 'Sujet notification n° '.str_random(8),
                        'body' => 'Contenu de la notification n° '.str_random(8)
                    ]
                ),
                'favoris' => true,
                'read_at' => null,
            ]);
            event(new NotifyCustomerMessage('new notifycation', $customer->id));
        }*/

        /*$event = Event::first();
        for($i=0; $i<100; $i++){
            Event::create([
                'nom' => $event->nom.'-'.$i,
                'date' => Carbon::now()->addDays($i),
                'prix' => $event->prix,
                'max_invite' => $event->max_invite,
                'description' => $event->description,
                'created_by' =>  $event->created_by,
                'updated_by' =>  $event->updated_by,
            ]);
        }*/

        event(new NotifyCustomerMessage('new notifycation', '2d9c5bef-baef-499f-9580-a973fa5be92d'));


    }
}
