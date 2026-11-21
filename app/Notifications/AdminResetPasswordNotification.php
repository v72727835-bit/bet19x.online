<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('admin.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $brand = \App\Models\SiteSetting::first()->site_name ?? 'FantasyXI';

        return (new MailMessage)
            ->subject("Reset Admin Password - {$brand}")
            ->line('Aapke admin account ke liye password reset request aayi hai.')
            ->action('Password Reset Karein', $url)
            ->line('Ye link 60 minute mein expire ho jayega.')
            ->line('Agar aapne ye request nahi ki, to is email ko ignore kar dein.');
    }
}
