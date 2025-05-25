<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResendVerification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $recepient;
    protected $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $recepient, $token)
    {
        $this->recepient = $recepient;
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
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Email Verification')
            ->greeting('Hello ' . $this->recepient->name . '!')
            ->line('Please click the link below to verify your account.')
            ->line(env('API_URL') . '/email/verify/' . $this->token);
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
