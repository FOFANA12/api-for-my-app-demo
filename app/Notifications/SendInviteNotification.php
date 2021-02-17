<?php

namespace App\Notifications;

use App\Helpers\ConfigApp;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class SendInviteNotification extends Notification
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    protected $event;
    public function __construct($event)
    {
        $this->event = $event;
        Log::info($this->event);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->from(env('MAIL_FROM_ADDRESS'), setting('app.name',config('app.name')))
            ->subject(__("Lettre d'invitation à l'évènement :name",['name' => $this->event['nom']]))
            ->greeting(__('Salut').' !')
            ->line(__("Vous recevez cet email car vous êtes invité pour participer à l'évènement : :name",['name' => $this->event['nom']]))
            ->line(__("Voici les détails sur l'évènement"))
            ->line(__("Date : :date",['date' => Carbon::parse($this->event['date'])->format(ConfigApp::date_format_php().' '.ConfigApp::heure_format_php())]))
            ->line(__("Nom : :name",['name' => $this->event['nom']]))
            ->line(__("Prix : :prix",['prix' => ConfigApp::moneyFormat($this->event['prix']).' '.setting('app_currency','MRU')]))
            ->line(__("Nombre de personne : :nombre",['nombre' => $this->event['max_invite']]))
            ->line(__('Veuillez vous connecter pour en savoir plus, par la suite vous pouvez confirmer ou décliner cet invitation.'))
            ->action(__('Connexion'), url(config('app.url_front').'/login'));
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
            //
        ];
    }
}
