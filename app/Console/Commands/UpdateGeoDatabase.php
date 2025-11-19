<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class UpdateGeoDatabase extends Command
{
    protected $signature = 'geo:maxmind-update {--local= : Utilise un fichier local (.mmdb ou .tar.gz) au lieu de télécharger}';

    protected $description = 'Télécharge la dernière base GeoLite2 City et l’installe dans database/maxmind';

    public function handle(): int
    {
        $targetPath = config('location.maxmind.local.path');
        $licenseKey = config('location.maxmind.license_key');
        $localOverride = $this->option('local');

        if (! $targetPath) {
            $this->error('Le chemin local vers la base MaxMind est introuvable (config/location.php).');

            return self::FAILURE;
        }

        if (! $localOverride && ! $licenseKey) {
            $this->error('Configurez MAXMIND_LICENSE_KEY dans votre .env pour lancer le téléchargement.');

            return self::FAILURE;
        }

        $tempDir = storage_path('app/maxmind/tmp-'.Str::random(6));
        File::ensureDirectoryExists($tempDir);

        try {
            if ($localOverride) {
                $mmdbPath = $this->resolveLocalFile($localOverride, $tempDir);
            } else {
                $mmdbPath = $this->downloadAndExtract($licenseKey, $tempDir);
            }

            File::ensureDirectoryExists(dirname($targetPath));
            File::copy($mmdbPath, $targetPath);

            $this->info("Base GeoLite2 installée : {$targetPath}");
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Erreur inattendue : '.$e->getMessage());

            return self::FAILURE;
        } finally {
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }

        return self::SUCCESS;
    }

    protected function resolveLocalFile(string $path, string $tempDir): string
    {
        if (! File::exists($path)) {
            throw new RuntimeException("Fichier local introuvable : {$path}");
        }

        if (Str::endsWith($path, '.mmdb')) {
            return $path;
        }

        if (Str::endsWith($path, '.tar') || Str::endsWith($path, '.tar.gz')) {
            return $this->extractArchive($path, $tempDir);
        }

        throw new RuntimeException('Format non supporté. Fournissez un .mmdb ou une archive .tar.gz.');
    }

    protected function downloadAndExtract(string $licenseKey, string $tempDir): string
    {
        $url = sprintf(
            'https://download.maxmind.com/app/geoip_download_by_token?edition_id=GeoLite2-City&license_key=%s&suffix=tar.gz',
            $licenseKey
        );

        $this->info('Téléchargement de GeoLite2 (MaxMind)...');
        $response = Http::timeout(60)->get($url);

        if (! $response->successful()) {
            throw new RuntimeException('Téléchargement impossible : '.$response->body());
        }

        $archivePath = $tempDir.'/GeoLite2-City.tar.gz';
        File::put($archivePath, $response->body());

        return $this->extractArchive($archivePath, $tempDir);
    }

    protected function extractArchive(string $archivePath, string $tempDir): string
    {
        $workingArchive = $archivePath;

        try {
            if (Str::endsWith($archivePath, '.gz')) {
                $phar = new \PharData($archivePath);
                $phar->decompress();
                unset($phar);

                $workingArchive = substr($archivePath, 0, -3);
            }

            $tar = new \PharData($workingArchive);
            $tar->extractTo($tempDir, null, true);
        } catch (\Throwable $e) {
            throw new RuntimeException('Impossible de décompresser l’archive MaxMind : '.$e->getMessage());
        }

        $mmdbFile = collect(File::allFiles($tempDir))
            ->first(fn ($file) => Str::endsWith($file->getFilename(), '.mmdb'));

        if (! $mmdbFile) {
            throw new RuntimeException('Fichier GeoLite2-City.mmdb introuvable dans l’archive.');
        }

        return $mmdbFile->getRealPath();
    }
}
