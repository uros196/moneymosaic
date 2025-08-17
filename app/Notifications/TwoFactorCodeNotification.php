<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification for sending two-factor authentication codes via email.
 *
 * This notification is triggered when a user needs to verify their identity
 * using two-factor authentication.
 */
class TwoFactorCodeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param string $code The two-factor authentication code to be sent
     */
    public function __construct(public string $code) {}

    /**
     * Get the notification delivery channels.
     *
     * @param object $notifiable The entity receiving the notification
     * @return array<string> The channels via which the notification will be sent
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param object $notifiable The entity receiving the notification
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your authentication code'))
            ->view('emails.twofactor-code', [
                'code' => $this->code,
            ]);
    }
}
