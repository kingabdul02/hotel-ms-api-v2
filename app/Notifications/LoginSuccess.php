<?php

namespace App\Notifications;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginSuccess extends Notification implements ShouldQueue
{
    use Queueable;
    protected $recepient;

    /**
     * Create a new notification instance.
     */
    public function __construct($recepient)
    {
        $this->recepient = $recepient;
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
            ->subject('Success Login Notification')
            ->greeting('Hello ' . $this->recepient->username . '!')
            ->line('You have successfully logged in to your account.')
            ->line('You successfully logged in to the NBTE Booking System @ ' . Carbon::now());
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'subject' => 'Login Successful',
            'content' => 'You have successfully logged in to the e-School platform.',
            'user_id' => $notifiable->id,
            'type' => 'success_login',
        ]);
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
