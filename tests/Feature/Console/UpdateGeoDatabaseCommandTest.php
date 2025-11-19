<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class UpdateGeoDatabaseCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_installs_local_mmdb_file(): void
    {
        $target = storage_path('framework/testing/geo/GeoLite2-City.mmdb');
        $source = storage_path('framework/testing/geo/source.mmdb');

        File::ensureDirectoryExists(dirname($source));
        File::put($source, 'dummy-mmdb');

        config()->set('location.maxmind.local.path', $target);

        $this->artisan('geo:maxmind-update', ['--local' => $source])
            ->expectsOutput("Base GeoLite2 installée : {$target}")
            ->assertExitCode(Command::SUCCESS);

        $this->assertFileExists($target);
        $this->assertSame('dummy-mmdb', File::get($target));

        File::deleteDirectory(dirname($target));
    }

    public function test_requires_license_when_no_local_file(): void
    {
        config()->set('location.maxmind.local.path', storage_path('framework/testing/geo/GeoLite2-City.mmdb'));
        config()->set('location.maxmind.license_key', null);

        $this->artisan('geo:maxmind-update')
            ->expectsOutput('Configurez MAXMIND_LICENSE_KEY dans votre .env pour lancer le téléchargement.')
            ->assertExitCode(Command::FAILURE);

        File::deleteDirectory(storage_path('framework/testing/geo'));
    }
}
