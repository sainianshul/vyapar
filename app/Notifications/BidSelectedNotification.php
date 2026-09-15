<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\RequestBid;

class BidSelectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $bid;

    

    /**
     * Create a new notification instance.
     */
    public function __construct(RequestBid $bid)
    {
        $this->bid = $bid;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels[] = \App\Channels\SafeFcmChannel::class;
        if (!in_array('database', $channels)) {
            array_unshift($channels, 'database');
        }
        return $channels;
    }

    /**
     * Get the FCM push notification representation.
     */
    public function toFcm(object $notifiable)
    {
        return [
            'title' => 'Bid Selected!',
            'body' => "The patient selected your bid! Waiting for their payment to confirm the booking.",
            'data' => [
                'type' => 'bid_selected',
                'bid_id' => $this->bid->id,
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
            'type' => 'bid_selected',
            'bid_id' => $this->bid->id,
            'message' => 'Your bid was selected.',
        ];
    }
}
