<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\RequestBid;

class NewBidPlacedNotification extends Notification implements ShouldQueue
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
        return ['database', \App\Channels\SafeFcmChannel::class];
    }

    /**
     * Get the FCM push notification representation.
     */
    public function toFcm(object $notifiable)
    {
        $nurseName = $this->bid->nurseProfile->user->name ?? 'A nurse';
        
        return [
            'title' => 'New Bid Received',
            'body' => "{$nurseName} has placed a bid of ₹{$this->bid->total_amount} on your request.",
            'data' => [
                'type' => 'new_bid',
                'bid_id' => $this->bid->id,
                'request_id' => $this->bid->care_request_id,
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
            'type' => 'new_bid',
            'bid_id' => $this->bid->id,
            'message' => 'New bid received on your request.',
        ];
    }
}
