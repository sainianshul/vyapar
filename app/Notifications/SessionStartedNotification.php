<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\BookingSession;

class SessionStartedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $session;

    public function __construct(BookingSession $session)
    {
        $this->session = $session;
    }

    public function via(object $notifiable): array
    {
        return ['database', \App\Channels\SafeFcmChannel::class];
    }

    public function toFcm(object $notifiable)
    {
        $nurseName = $this->session->booking->nurse->user->name ?? 'The nurse';
        return [
            'title' => 'Session Started',
            'body' => "{$nurseName} has started the session.",
            'data' => [
                'type' => 'session_started',
                'session_id' => $this->session->id,
                'booking_id' => $this->session->booking_id,
            ]
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'session_started',
            'session_id' => $this->session->id,
            'message' => 'Session has started.',
        ];
    }
}
