<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\BookingSession;

class SessionEndedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $session;

    public function __construct(BookingSession $session)
    {
        $this->session = $session;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', \App\Channels\SafeFcmChannel::class];
    }

    public function toFcm(object $notifiable)
    {
        return [
            'title' => 'Session Ended',
            'body' => "Session ended. Please rate the service.",
            'data' => [
                'type' => 'session_ended',
                'session_id' => $this->session->id,
                'booking_id' => $this->session->booking_id,
            ]
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'session_ended',
            'session_id' => $this->session->id,
            'message' => 'Session has ended.',
        ];
    }
}
