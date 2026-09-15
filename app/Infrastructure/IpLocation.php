<?php

namespace App\Infrastructure;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class IpLocation
{
    private array $data = [];
    private bool $success = false;

    public function __construct(string $ip)
    {
        // Skip local IPs to prevent API failures or wasted calls
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            $this->data = ['message' => 'Localhost'];
            return;
        }

        // Cache the result for 24 hours (86400 seconds) to avoid rate limits and improve performance
        $this->data = Cache::remember('ip_location_' . $ip, 86400, function () use ($ip) {
            try {
                // Free API, no auth required, returns JSON
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");
                
                if ($response->successful() && $response->json('status') === 'success') {
                    return $response->json();
                }
            } catch (\Exception $e) {
                // Log exception if needed, but don't break the page
            }
            return [];
        });

        if (!empty($this->data) && ($this->data['status'] ?? '') === 'success') {
            $this->success = true;
        }
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getCountry(): ?string
    {
        return $this->data['country'] ?? null;
    }

    public function getCity(): ?string
    {
        return $this->data['city'] ?? null;
    }

    public function getRegion(): ?string
    {
        return $this->data['regionName'] ?? null;
    }

    public function getZip(): ?string
    {
        return $this->data['zip'] ?? null;
    }

    public function getIsp(): ?string
    {
        return $this->data['isp'] ?? null;
    }

    public function getLat(): ?float
    {
        return isset($this->data['lat']) ? (float)$this->data['lat'] : null;
    }

    public function getLon(): ?float
    {
        return isset($this->data['lon']) ? (float)$this->data['lon'] : null;
    }

    public function getTimezone(): ?string
    {
        return $this->data['timezone'] ?? null;
    }
}
