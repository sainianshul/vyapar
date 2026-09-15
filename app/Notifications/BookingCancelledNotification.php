<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Booking;
use App\Channels\TwilioChannel;

class BookingCancelledNotification extends Notification implements ShouldQueue
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
        if ($this->targetRole === 'patient') {
            return "URGENT: Booking #{$this->booking->reference_id} cancelled. Your refund has been initiated.";
        } else {
            return "URGENT: Booking #{$this->booking->reference_id} was cancelled by the patient. Do not visit.";
        }
    }

    public function toFcm(object $notifiable)
    {
        return [
            'title' => 'Booking Cancelled',
            'body' => $this->targetRole === 'patient'
                ? "URGENT: Booking #{$this->booking->reference_id} cancelled. Your refund has been initiated."
                : "URGENT: Booking #{$this->booking->reference_id} was cancelled by the patient. Do not visit.",
            'data' => [
                'type' => 'booking_cancelled',
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
            ->subject("URGENT: Booking Cancelled - #{$this->booking->reference_id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("The booking #{$this->booking->reference_id} scheduled for {$this->booking->start_date} has been cancelled.")
            ->line("Please DO NOT visit the patient location.")
            ->action('View Schedule', url('/'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_cancelled',
            'booking_id' => $this->booking->id,
            'message' => 'Booking was cancelled.',
        ];
    }
}
