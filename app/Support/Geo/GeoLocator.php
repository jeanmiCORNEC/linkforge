<?php

namespace App\Support\Geo;

use Illuminate\Support\Facades\Cache;
use Stevebauman\Location\Facades\Location;

class GeoLocator
{
    /**
     * Retourne les informations de géolocalisation (country code + city) pour l'adresse IP donnée.
     * Les résultats sont mis en cache 24h pour préserver les accès disque.
     */
    public function lookup(?string $ip): GeoLookupResult
    {
        $resolvedIp = $this->resolveIp($ip);

        if (! $this->shouldLookup($resolvedIp)) {
            return GeoLookupResult::empty();
        }

        $cacheKey = "geo:{$resolvedIp}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($resolvedIp) {
            try {
                $position = Location::get($resolvedIp);
            } catch (\Throwable) {
                return GeoLookupResult::empty();
            }

            if (! $position) {
                return GeoLookupResult::empty();
            }

            $countryCode = $position->countryCode ?? null;
            $countryName = $position->countryName ?? null;

            if ($countryCode) {
                $countryCode = strtoupper($countryCode);
            }

            return new GeoLookupResult(
                $countryCode,
                $countryName,
                $position->cityName ?: null,
            );
        });
    }

    protected function shouldLookup(?string $ip): bool
    {
        if (! $ip) {
            return false;
        }

        // On ignore les IP privées / locales pour éviter des faux positifs.
        if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0'], true)) {
            return false;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        // FILTER_FLAG_NO_PRIV_RANGE + NO_RES_RANGE => uniquement IP routables publiquement.
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    protected function resolveIp(?string $ip): ?string
    {
        if (config('location.testing.enabled')) {
            return config('location.testing.ip', $ip);
        }

        return $ip;
    }
}

class GeoLookupResult
{
    public function __construct(
        public readonly ?string $countryCode = null,
        public readonly ?string $countryName = null,
        public readonly ?string $city = null,
    ) {
    }

    public static function empty(): self
    {
        return new self();
    }
}
