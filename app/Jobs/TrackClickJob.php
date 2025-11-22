<?php

namespace App\Jobs;

use App\Models\Click;
use App\Support\Geo\GeoLocator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TrackClickJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $trackedLinkId,
        public string $ip,
        public string $userAgent,
        public ?string $referrer
    ) {}

    /**
     * Execute the job.
     */
    public function handle(GeoLocator $geoLocator): void
    {
        // 1. User Agent Parsing
        $device = $this->guessDeviceFromUserAgent($this->userAgent);
        $browser = $this->guessBrowserFromUserAgent($this->userAgent);
        $visitorHash = hash('sha256', $this->ip . '|' . $this->userAgent);

        // 2. Geo Lookup
        $geoCountry = null;
        $geoCity = null;

        try {
            $geoLocation = $geoLocator->lookup($this->ip);
            if ($geoLocation) {
                $geoCountry = $geoLocation->countryCode ?? $geoLocation->countryName;
                $geoCity = $geoLocation->city;
            }
        } catch (\Throwable $e) {
            // On log mais on ne fail pas le job pour ça
            Log::warning('Geo lookup failed in Job', [
                'ip' => $this->ip,
                'error' => $e->getMessage(),
            ]);
        }

        // 3. DB Insert
        try {
            Click::create([
                'tracked_link_id' => $this->trackedLinkId,
                'ip_address'      => $this->ip,
                'user_agent'      => $this->userAgent,
                'referrer'        => $this->referrer,
                'device'          => $device,
                'browser'         => $browser,
                'os'              => null,
                'visitor_hash'    => $visitorHash,
                'country'         => $geoCountry,
                'city'            => $geoCity,
            ]);
        } catch (\Throwable $e) {
            Log::error('Click logging failed in Job', [
                'tracked_link_id' => $this->trackedLinkId,
                'error' => $e->getMessage(),
            ]);
            // On pourrait throw $e pour retry le job, mais pour des stats c'est discutable.
            // Pour l'instant on log juste.
        }
    }

    protected function guessDeviceFromUserAgent(string $ua): string
    {
        $ua = strtolower($ua);

        if (str_contains($ua, 'iphone') || str_contains($ua, 'android') && str_contains($ua, 'mobile')) {
            return 'mobile';
        }

        if (str_contains($ua, 'ipad') || str_contains($ua, 'tablet')) {
            return 'tablet';
        }

        return 'desktop';
    }

    protected function guessBrowserFromUserAgent(string $ua): string
    {
        $ua = strtolower($ua);

        if (str_contains($ua, 'chrome')) {
            return 'Chrome';
        }

        if (str_contains($ua, 'firefox')) {
            return 'Firefox';
        }

        if (str_contains($ua, 'safari') && ! str_contains($ua, 'chrome')) {
            return 'Safari';
        }

        if (str_contains($ua, 'edg')) {
            return 'Edge';
        }

        return 'Other';
    }
}
