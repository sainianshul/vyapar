<?php

namespace App\Listeners;

use App\Models\CommunicationLog;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;
use Exception;

class LogNotificationSent
{
    /**
     * Handle the event.
     */
    public function handle(NotificationSent $event): void
    {
        try {
            $notifiable = $event->notifiable;
            $notification = $event->notification;
            $channel = $event->channel;
            
            // Get the destination (e.g. email address or phone number)
            $destination = null;
            if (method_exists($notifiable, 'routeNotificationFor')) {
                $destination = $notifiable->routeNotificationFor($channel, $notification);
            } elseif ($channel === 'mail' && isset($notifiable->email)) {
                $destination = $notifiable->email;
            } elseif ($channel === 'twilio' && isset($notifiable->phone)) {
                $destination = $notifiable->phone;
            }

            // Extract some basic content if possible
            $content = '';
            if (method_exists($notification, 'toTwilio')) {
                $content = $notification->toTwilio($notifiable);
            } elseif (method_exists($notification, 'toMail')) {
                $content = 'Mail sent.';
            } elseif (method_exists($notification, 'toFcm')) {
                $content = 'Push notification sent.';
            }

            CommunicationLog::create([
                'notifiable_type' => $notifiable instanceof \Illuminate\Database\Eloquent\Model ? get_class($notifiable) : null,
                'notifiable_id' => $notifiable instanceof \Illuminate\Database\Eloquent\Model ? $notifiable->id : null,
                'channel' => $channel,
                'type' => get_class($notification),
                'destination' => is_string($destination) ? $destination : json_encode($destination),
                'content' => is_string($content) ? $content : json_encode($content),
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to log notification: ' . $e->getMessage());
        }
    }
}
