<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeCustomerNotification extends Notification
{

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    protected $pass;

    public function __construct(String $pass)
    {
        $this->pass = $pass;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('Bienvenue sur :name',['name'=>setting('app.name',config('app.name'))]))
            ->greeting(__('Salut').' !')
            ->line(__('Vous recevez cet email car vous êtes actuellement inscrit comme client sur :name.',['name'=>setting('app.name',config('app.name'))]))
            ->line(__('Voici les identifiants de votre compte que vous ne devez communiquer à personne.'))
            ->line(__('Adresse électronique : :email',['email'=>$notifiable->email]))
            ->line(__('Mot de passe : :password',['password'=>$this->pass]))
            ->action(__('Connexion'), url(config('app.url_front').'/login'));
    }
}
