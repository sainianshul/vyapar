<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NurseProfileStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public $status;
    public $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct($status, $reason = null)
    {
        $this->status = $status; // 'approved' or 'rejected'
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail', \App\Channels\SafeFcmChannel::class];
    }

    /**
     * Get the FCM push notification representation.
     */
    public function toFcm(object $notifiable)
    {
        return [
            'title' => 'Profile Status Updated',
            'body' => "Your profile has been {$this->status}.",
            'data' => [
                'type' => 'profile_status_changed',
                'status' => $this->status,
            ]
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject("Profile Update: " . ucfirst($this->status))
                    ->markdown('emails.nurses.profile-status-changed', [
                        'user' => $notifiable,
                        'status' => $this->status,
                        'reason' => $this->reason
                    ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'status' => $this->status,
            'message' => "Your profile has been {$this->status}.",
        ];
    }
}
