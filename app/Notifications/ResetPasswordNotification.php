<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = rtrim(config('app.frontend_url'), '/')
            .'/reset-password?'.http_build_query([
                'token' => $this->token,
                'email' => $notifiable->email,
            ], '', '&', PHP_QUERY_RFC3986);

        return (new MailMessage)
            ->subject('Restablece tu contraseña')
            ->greeting('Hola '.$notifiable->name.'!')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
            ->action('Restablecer contraseña', $url)
            ->line('Este enlace expirará en '.config('auth.passwords.users.expire').' minutos.')
            ->line('Si no solicitaste este cambio, ignora este correo.');
    }
}
