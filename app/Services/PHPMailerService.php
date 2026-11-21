<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class PHPMailerService
{
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST', '127.0.0.1');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION', PHPMailer::ENCRYPTION_STARTTLS);
            $mail->Port       = (int) env('MAIL_PORT', 587);

            $fromName = env('MAIL_FROM_NAME') ?: (\App\Models\SiteSetting::first()->site_name ?? 'FantasyXI');
            $mail->setFrom(env('MAIL_FROM_ADDRESS', 'hello@fantasyxi.local'), $fromName);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;

            $mail->send();
            return true;
        } catch (Exception $e) {
            Log::error('PHPMailer send failed: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
