<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     */
    public string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords', 'users').'.expire', 60);

        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        // Gunakan URL logo publik HTTPS agar proxy gambar Gmail dapat memuat favicon dengan sempurna
        $logoUrl = env('MAIL_LOGO_URL', (
            str_starts_with((string) config('app.url'), 'https://')
                ? asset('favicon.png')
                : 'https://siswamartid.my.id/favicon.png'
        ));

        $userName = $notifiable->name ?? null;

        $mail = (new MailMessage)
            ->subject('Reset Password SiswaMart')
            ->view('emails.reset-password', [
                'url' => $url,
                'expire' => $expire,
                'user' => $notifiable,
                'userName' => $userName,
                'logoUrl' => $logoUrl,
            ])
            ->text('emails.reset-password-text', [
                'url' => $url,
                'expire' => $expire,
                'user' => $notifiable,
                'userName' => $userName,
            ]);

        $mail->actionUrl = $url;
        $mail->actionText = 'Atur Ulang Password';

        return $mail;
    }
}
