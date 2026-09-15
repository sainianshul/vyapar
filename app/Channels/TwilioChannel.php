<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Log;

class TwilioChannel
{
    public function __construct(private SmsServiceInterface $smsService)
    {
    }

    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toTwilio')) {
            return;
        }

        $message = $notification->toTwilio($notifiable);

        $phone = null;
        if (method_exists($notifiable, 'routeNotificationForTwilio')) {
            $phone = $notifiable->routeNotificationForTwilio($notification);
        } elseif (method_exists($notifiable, 'routeNotificationFor')) {
            $phone = $notifiable->routeNotificationFor('twilio', $notification);
        }
        $phone = $phone ?? $notifiable->phone;

        if ($phone && $message) {
            try {
                $this->smsService->send($phone, $message);
            } catch (\Exception $e) {
                Log::error('TwilioChannel failed: ' . $e->getMessage());
                throw $e; // Throw so Laravel knows it failed
            }
        }
    }
}
