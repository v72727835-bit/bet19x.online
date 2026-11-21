<?php

namespace App\Notifications\Channels;

use App\Services\PHPMailerService;
use Illuminate\Notifications\Notification;

class PHPMailerChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (! method_exists($notification, 'toPHPMailer')) {
            return;
        }

        $data = $notification->toPHPMailer($notifiable);

        $email = method_exists($notifiable, 'getEmailForPasswordReset')
            ? $notifiable->getEmailForPasswordReset()
            : $notifiable->email;

        PHPMailerService::send(
            $email,
            $notifiable->name ?? '',
            $data['subject'],
            $data['body']
        );
    }
}
