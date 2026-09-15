<?php

namespace App\Contracts;

interface SmsServiceInterface
{
    /**
     * Send an SMS message to a specific phone number.
     *
     * @param string $phone The recipient's phone number (with country code).
     * @param string $message The content of the SMS.
     * @return bool True on success, false on failure.
     */
    public function send(string $phone, string $message): bool;
}
