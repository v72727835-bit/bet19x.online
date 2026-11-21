<?php

namespace App\Notifications;

use App\Notifications\Channels\PHPMailerChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token)
    {
    }

    public function via($notifiable): array
    {
        return [PHPMailerChannel::class];
    }

    public function toPHPMailer($notifiable): array
    {
        $setting = \App\Models\SiteSetting::first();
        $brand = $setting->site_name ?? 'FantasyXI';
        $color = $setting->primary_color ?? '#FFB627';

        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $body = "
            <div style='font-family:Arial,sans-serif; background:#14152B; padding:32px;'>
                <div style='max-width:480px; margin:0 auto; background:#1D1E3D; border-radius:12px; padding:32px; text-align:center;'>
                    <h2 style='color:#F4F3FA; font-family:Arial,sans-serif;'>{$brand}</h2>
                    <p style='color:#8A8CB3;'>Aapke account ke liye password reset request aayi hai.</p>
                    <p style='margin:28px 0;'>
                        <a href='{$url}' style='background:{$color};color:#14152B;padding:12px 28px;text-decoration:none;border-radius:8px;font-weight:bold;display:inline-block;'>Password Reset Karein</a>
                    </p>
                    <p style='color:#8A8CB3; font-size:12px;'>Ye link 60 minute mein expire ho jayega. Agar aapne ye request nahi ki, is email ko ignore kar dein.</p>
                </div>
            </div>
        ";

        return [
            'subject' => "Password Reset - {$brand}",
            'body' => $body,
        ];
    }
}
