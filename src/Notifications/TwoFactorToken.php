<?php

namespace Hydrat\Laravel2FA\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\NexmoMessage;

class TwoFactorToken extends Notification
{
    /**
     * The 2FA token.
     *
     * @var string
     * @access protected
     */
    protected string $token = '';

    /**
     * Notification constructor.
     *
     * @param string $token The 2FA token.
     */
    public function __construct(string $token = '')
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
            ->subject('Your two factor code.')
            ->greeting('Hello!')
            ->line('Your two factor code is ' . $this->token)
            // ->action('Verify Here', route('auth.2fa.index'))
            ->line('This code will expire in 10 minutes')
            ->line('If you have not tried to login, please change your password immediately.');
    }

    /**
     * Get the Vonage / SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return NexmoMessage
     */
    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
                    ->content('Your two-factor token is ' . $this->token)
                    ->from('MYAPP');
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
            'token' => $this->token,
        ];
    }
}
