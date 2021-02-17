<?php

namespace App\Jobs;

use App\Events\NotifyCustomerMessage;
use App\Models\Customer;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Notifications\SendInviteNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class SendInvitationEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $id;
    protected $type;
    protected $customerId;
    public function __construct($type, $customerId, $id)
    {
        $this->type = $type;
        $this->customerId = $customerId;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();
        try {

            if($this->type == "re-send-customer"){
                $event = Event::findOrFail($this->id);
                $invite = EventInvitation::where('event',$this->id)->where('customer',$this->customerId)->first();

                $customer = Customer::findOrFail($this->customerId);
                $customer->notify(new SendInviteNotification($event));

                $invite->notify = $invite->notify < 0 ? 0 : $invite->notify+1;
                $invite->save();

                $url = url(config('app.url_front').'/events/my-invitations/view/'.$event->id);
                $customer->notificationMessages()->create([
                    'type' => 'success',
                    'data' => json_encode(
                        [
                            'from' => 'HADES Consulting',
                            'subject' => 'Invitation pour l\'évènement '.$event->nom,
                            'body' => "<h2>Bonjour</h2>,<br/>
                                        Vous recevez cette notification car vous êtes invité de participer à l'évènement $event->nom. <br/>
                                        Veuillez cliquer <a href='$url'>ici</a> pour plus de détails."
                        ]
                    ),
                    'favoris' => false,
                    'read_at' => null,
                ]);
                event(new NotifyCustomerMessage('new notifycation', $customer->id));
            }else{
                $event = Event::findOrFail($this->id);
                $invites = EventInvitation::where('event',$this->id)->get();
                $url = url(config('app.url_front').'/events/my-invitations/view/'.$event->id);

                foreach ($invites as $invite){
                    //if($invite->notify <= 0){
                        $customer = Customer::findOrFail($invite->customer);
                        $customer->notify(new SendInviteNotification($event));

                        $customer->notificationMessages()->create([
                            'type' => 'success',
                            'data' => json_encode(
                                [
                                    'from' => 'HADES Consulting',
                                    'subject' => 'Invitation pour l\'évènement '.$event->nom,
                                    'body' => "<h5>Bonjour</h5>,<br/>
                                        Vous recevez cette notification car vous êtes invité de participer à l'évènement $event->nom. <br/>
                                        Veuillez cliquer <a href='$url'>ici</a> pour plus de détails."
                                ]
                            ),
                            'favoris' => false,
                            'read_at' => null,
                        ]);
                        event(new NotifyCustomerMessage('new notifycation', $customer->id));

                        $invite->notify = $invite->notify < 0 ? 0 : $invite->notify+1;
                        $invite->save();
                    }
               // }
            }

            DB::commit();
        }catch (\Exception $e) {
            DB::rollback();
        }
    }
}
