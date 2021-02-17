<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class CustomerPasswordResetNotification extends ResetPasswordNotification implements ShouldQueue
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
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
            ->subject(__('Réinitialisation du mot de passe'))
            ->greeting(__('Salut').' !')
            ->line(__('Vous recevez cet email car nous avons reçu une demande de réinitialisation du mot de passe pour votre compte.'))
            ->action(__('Réinitialiser le mot de passe'), url(config('app.url_front').'/reset-password/'.$this->token).'/'.urlencode($notifiable->email))
            ->line(__('Ce lien de réinitialisation de mot de passe expirera dans :count minutes.', ['count' => config('auth.passwords.customers.expire')]))
            ->line(__('Si vous n\'avez pas demandé de réinitialisation de mot de passe, aucune autre action n\'est requise.'));
    }

}
