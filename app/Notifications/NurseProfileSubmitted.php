<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NurseProfileSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
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
            'title' => 'Profile Submitted',
            'body' => 'Your profile has been submitted and is under review.',
            'data' => [
                'type' => 'profile_submitted',
            ]
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Profile Submitted Successfully')
                    ->markdown('emails.nurses.profile-submitted', [
                        'user' => $notifiable
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
            'message' => 'Your profile has been submitted and is under review.',
        ];
    }
}
