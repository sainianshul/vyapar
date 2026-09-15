<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\CareRequest;

class NewCareRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $careRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(CareRequest $careRequest)
    {
        $this->careRequest = $careRequest;
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

    /**
     * Get the FCM push notification representation.
     */
    public function toFcm(object $notifiable)
    {
        return [
            'title' => 'New Care Request Nearby',
            'body' => "A new care request (#{$this->careRequest->reference_id}) is available in your area. Place a bid now!",
            'data' => [
                'type' => 'new_request',
                'request_id' => $this->careRequest->id,
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
            'type' => 'new_request',
            'request_id' => $this->careRequest->id,
            'message' => 'New Care Request in your area.',
        ];
    }
}
