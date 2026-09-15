<?php
// app/Infrastructure/Sms/SmsInterface.php
namespace App\Infrastructure\Sms;

interface SmsInterface
{
    public function send(string $phone, string $message): bool;
}