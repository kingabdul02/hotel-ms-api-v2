<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuccessPayment extends Notification implements ShouldQueue
{
    use Queueable;
    public $booking_id;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $booking_id)
    {
        $this->$booking_id = $booking_id;
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
                    ->line('Thank you for choosing NBTE Hotel')
                    ->line('Your payment was receive successfully. Click th link below to view details of your reservation:')
                    ->action('Reservation Details', url(env('CLIENT_URL') . '/booking/user/account/booking/details/' . $this->booking_id));
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
