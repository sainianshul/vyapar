<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationLog extends Model
{
    public $timestamps = false; // We will only use created_at

    const STATUS_PENDING = 1;
    const STATUS_SENT = 2;
    const STATUS_FAILED = 3;

    protected $fillable = [
        'number',
        'message',
        'status',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'status' => 'integer',
    ];

    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SENT => 'Sent',
            self::STATUS_FAILED => 'Failed',
        ];
    }

    public function getStatusNameAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? 'Unknown';
    }

    public function getStatusColorAttribute(): string
    {
        $colors = [
            self::STATUS_PENDING => 'orange',
            self::STATUS_SENT => 'green',
            self::STATUS_FAILED => 'red',
        ];
        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Safely log a communication without breaking the application flow.
     */
    public static function log(string $number, string $message, int $status = self::STATUS_PENDING)
    {
        try {
            self::create([
                'number' => $number,
                'message' => $message,
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            // Silently ignore to prevent main application flow from breaking
            \Log::error('Failed to log communication: ' . $e->getMessage());
        }
    }
}
