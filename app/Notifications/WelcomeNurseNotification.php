<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNurseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $password;

    public function __construct($password)
    {
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to VCanCares - Your Account is Ready')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your caregiver account has been created by our administration team.')
            ->line('You can now log in using the following credentials:')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Password:** ' . $this->password)
            ->action('Login Now', url('/login'))
            ->line('We recommend changing your password after your first login.')
            ->line('Thank you for joining our platform!');
    }
}
