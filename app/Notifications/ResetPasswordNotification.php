<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expiresIn = config('auth.passwords.users.expire', 60);

        return (new MailMessage)
            ->subject('Restablece tu contrasena — PideAqui')
            ->greeting('Hola!')
            ->line('Recibimos una solicitud para restablecer la contrasena de tu cuenta en PideAqui.')
            ->line('Haz clic en el boton de abajo para crear una nueva contrasena:')
            ->action('Restablecer contrasena', $url)
            ->line("Este enlace expirara en {$expiresIn} minutos.")
            ->line('Si no solicitaste este cambio, puedes ignorar este mensaje. Tu contrasena actual no se modificara.')
            ->salutation('Saludos, el equipo de PideAqui');
    }
}
