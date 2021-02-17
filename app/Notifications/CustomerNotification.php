<?php

namespace App\Notifications;

use App\Models\NotificationMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CustomerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $notificationMessage;

    public function __construct(NotificationMessage $notificationMessage)
    {
        $this->notificationMessage = $notificationMessage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }


    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'from' => $this->notificationMessage->getFrom(),
            'subject' => $this->notificationMessage->getSubject(),
            'body' => $this->notificationMessage->getBody(),
        ];
    }

    /*public function toBroadcast($notifiable){
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function broadcastType(){
        return 'new-notification';
    }*/
    public static function toText($data){
        $text = __('Bienvenue :name',['name'=>$data['name']]).'<br/>';
        $text .= __('Nous vous informons que votre inscription s\'est déroulée avec succès.');
        return $text;
    }
}
