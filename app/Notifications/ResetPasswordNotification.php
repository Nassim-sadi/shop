<?php

namespace App\Notifications;

use Ichtrojan\Otp\Otp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    use Queueable;
    public $message;
    public $subject;
    public $fromEmail;
    public $mailer;
    public $url;
    private $otp;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $url)
    {
        $this->url = $url;
        $this->message = 'use the below code for reset password';
        $this->subject = 'password resetting';
        $this->fromEmail = env('MAIL_FROM_ADDRESS');
        $this->otp = new Otp();
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
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     $otp = $this->otp->generate($notifiable->email, 'numeric', 6, 15);
    //     return (new MailMessage)
    //         ->from($this->fromEmail)
    //         ->subject($this->subject)
    //         ->line($this->message)
    //         ->line("code :" . $otp->token);
    // }

    // /**
    //  * Get the array representation of the notification.
    //  *
    //  * @return array<string, mixed>
    //  */
    // public function toArray(object $notifiable): array
    // {
    //     return [
    //         //
    //     ];
    // }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Réinitialiser le mot de passe')
            // ->greeting('Salut' . ' ' . $notifiable->firstname . ' ' . $notifiable->lastname . '،')
            ->line('La réinitialisation de votre mot de passe est simple.')
            ->line('Il vous suffit de cliquer sur le bouton ci-dessous et de suivre les instructions.')
            ->action('Réinitialiser le mot de passe', $this->url)
            ->line('Si vous ne soumettez pas cette demande, veuillez ignorer cet e-mail.')
            ->salutation('Merci d\'utiliser notre application.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
