<?php

namespace App\Services\Sms;

use App\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Exception;

class TwilioSmsService implements SmsServiceInterface
{
    /**
     * Send an SMS using Twilio.
     * Engineered for stability: Catches all exceptions and logs them properly
     * so that the application never crashes if the SMS gateway is down.
     */
    public function send(string $phone, string $message): bool
    {
        try {
            $sid    = env('TWILIO_SID');
            $token  = env('TWILIO_AUTH_TOKEN');
            $from   = env('TWILIO_FROM');

            if (empty($sid) || empty($token) || empty($from)) {
                Log::warning('TwilioSmsService: Missing Twilio credentials in .env');
                return false;
            }

            // Optional: If you don't have the Twilio SDK installed yet, 
            // this will fail gracefully instead of throwing a fatal error.
            if (!class_exists(Client::class)) {
                Log::error('TwilioSmsService: Twilio SDK is not installed. Run "composer require twilio/sdk".');
                return false;
            }

            $twilio = new Client($sid, $token);

            $twilio->messages->create(
                $phone, // To
                [
                    "from" => $from,
                    "body" => $message
                ]
            );

            Log::info("TwilioSmsService: SMS sent successfully to {$phone}");
            return true;

        } catch (Exception $e) {
            // Never throw the exception. Log it and return false to ensure the app continues running.
            Log::error("TwilioSmsService Failed: " . $e->getMessage(), [
                'phone' => $phone,
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
}
