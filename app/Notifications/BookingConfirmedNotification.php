<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Channels\TwilioChannel;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $booking;
    public $targetRole;

    public function __construct(Booking $booking, string $targetRole)
    {
        $this->booking = $booking;
        $this->targetRole = $targetRole;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', TwilioChannel::class]; // Database & SMS
        if ($this->targetRole === 'nurse') {
            $channels[] = 'mail';
        }
        $channels[] = \App\Channels\SafeFcmChannel::class;
        return $channels;
    }

    public function toTwilio(object $notifiable): string
    {
        $nurseName = $this->booking->nurse->user->name ?? 'your nurse';
        if ($this->targetRole === 'patient') {
            return "Your booking #{$this->booking->reference_id} is confirmed with Nurse {$nurseName}.";
        } else {
            return "New Booking #{$this->booking->reference_id} confirmed. Please check your schedule.";
        }
    }

    public function toFcm(object $notifiable)
    {
        $nurseName = $this->booking->nurse->user->name ?? 'your nurse';
        return [
            'title' => 'Booking Confirmed!',
            'body' => $this->targetRole === 'patient' 
                ? "Your booking #{$this->booking->reference_id} is confirmed with Nurse {$nurseName}."
                : "New Booking #{$this->booking->reference_id} confirmed. Please check your schedule.",
            'data' => [
                'type' => 'booking_confirmed',
                'booking_id' => $this->booking->id,
            ]
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject("New Booking Confirmed: #{$this->booking->reference_id}")
                    ->greeting("Hello {$notifiable->name},")
                    ->line("You have a new booking confirmed for {$this->booking->start_date}.")
                    ->line("Booking ID: {$this->booking->reference_id}")
                    ->line("Please check your schedule in the app for more details.")
                    ->action('View Dashboard', url('/'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_confirmed',
            'booking_id' => $this->booking->id,
            'message' => 'Booking confirmed.',
        ];
    }
}
