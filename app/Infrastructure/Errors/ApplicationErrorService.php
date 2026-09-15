<?php

namespace App\Infrastructure\Error;

use App\Models\ApplicationError;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ApplicationErrorService
{
    public function handle(Throwable $e, Request $request): string
    {
        $errorId = $this->generateErrorId();

        try {

            $severity = $this->detectSeverity($e);

            $fingerprint = $this->generateFingerprint($e);

            $applicationError = ApplicationError::create([
                'error_id' => $errorId,
                'user_id' => auth()->id(),

                'message' => $this->safeMessage($e),
                'exception' => get_class($e),

                'file' => $e->getFile(),
                'line' => $e->getLine(),

                'trace' => $e->getTraceAsString(),

                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),

                'request_data' => $this->requestData($request),

                'severity' => $severity,
                'status' => ApplicationError::STATUS_PENDING,

                'fingerprint' => $fingerprint,
            ]);

            if ($this->shouldNotify($applicationError)) {

                // Future:
                // Mail
                // Slack
                // Telegram
                // Queue Job
            }

        } catch (Throwable $loggingException) {
            Log::error('Application error logging failed', [
                'message' => $loggingException->getMessage(),
            ]);
        }

        report($e);

        return $errorId;
    }

    // Generate error id.
    private function generateErrorId(): string
    {
        return 'ERR-' . strtoupper(Str::random(10));
    }

    // Detect severity.
    private function detectSeverity(Throwable $e): int
    {

        // Database errors
        if ($e instanceof QueryException) {

            return ApplicationError::SEVERITY_HIGH;
        }


        // Http exceptions
        if ($e instanceof HttpExceptionInterface) {

            if ($e->getStatusCode() >= 500) {

                return ApplicationError::SEVERITY_HIGH;
            }

            return ApplicationError::SEVERITY_LOW;
        }

        return ApplicationError::SEVERITY_MEDIUM;
    }

    // Generate stable fingerprint.
    private function generateFingerprint(Throwable $e): string
    {
        return md5(
            get_class($e) .
            '|' .
            $e->getMessage() .
            '|' .
            $e->getFile() .
            '|' .
            $e->getLine()
        );
    }

    // Safe exception message.
    private function safeMessage(Throwable $e): string
    {
        return Str::limit($e->getMessage(), 1000);
    }

    //  Remove sensitive request data.
    private function requestData(Request $request): array
    {
        return $request->except([
            'password',
            'password_confirmation',
            'token',
            'access_token',
            'refresh_token',
        ]);
    }

    // Deciding whether notification should be sent.   
    private function shouldNotify(ApplicationError $applicationError): bool
    {
        return in_array($applicationError->severity, [
            ApplicationError::SEVERITY_HIGH,
            ApplicationError::SEVERITY_CRITICAL,
        ]);
    }
}