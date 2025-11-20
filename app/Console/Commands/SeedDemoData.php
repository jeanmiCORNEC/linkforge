<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\Click;
use App\Models\Link;
use App\Models\Source;
use App\Models\TrackedLink;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SeedDemoData extends Command
{
    protected $signature = 'demo:seed {--clicks=200 : Nombre de clics à générer}';

    protected $description = 'Génère un jeu de données de démonstration (utilisateur, campagnes, sources, liens, clics).';

    public function handle(): int
    {
        $clicksTarget = (int) $this->option('clicks');

        $user = User::firstOrCreate([
            'email' => 'demo@linkforge.test',
        ], [
            'name' => 'Demo User',
            'username' => 'demo',
            'password' => bcrypt('password'),
            'plan' => 'pro',
        ]);

        $this->info("Utilisateur démo : {$user->email} / password");

        // Campagnes + sources + liens
        $campaigns = Campaign::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        $links = Link::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $sources = collect();
        foreach ($campaigns as $campaign) {
            $sources = $sources->merge(
                Source::factory()->count(2)->create([
                    'user_id' => $user->id,
                    'campaign_id' => $campaign->id,
                ])
            );
        }

        // Tracked links pour chaque source/ lien
        $trackedLinks = collect();
        foreach ($sources as $source) {
            foreach ($links as $link) {
                $trackedLinks->push(
                    TrackedLink::create([
                        'user_id' => $user->id,
                        'link_id' => $link->id,
                        'source_id' => $source->id,
                        'tracking_key' => Str::random(10),
                        'short_code' => null, // auto-généré par l'observer created
                    ])
                );
            }
        }

        $countries = ['FR', 'US', 'CA', 'BE', 'DE'];
        $devices = ['mobile', 'desktop', 'tablet'];
        $browsers = ['Chrome', 'Safari', 'Firefox', 'Edge'];

        $clicks = [];
        for ($i = 0; $i < $clicksTarget; $i++) {
            $tracked = $trackedLinks->random();
            $createdAt = Carbon::now()->subDays(rand(0, 6))->subHours(rand(0, 23))->subMinutes(rand(0, 59));

            $clicks[] = [
                'tracked_link_id' => $tracked->id,
                'ip_address'      => '192.0.2.' . rand(1, 254),
                'user_agent'      => 'DemoBot/1.0',
                'referrer'        => Arr::random(['https://tiktok.com', 'https://instagram.com', 'https://youtube.com', null]),
                'device'          => Arr::random($devices),
                'browser'         => Arr::random($browsers),
                'visitor_hash'    => Str::uuid()->toString(),
                'country'         => Arr::random($countries),
                'city'            => Arr::random(['Paris', 'Lyon', 'NYC', 'LA', 'Berlin', null]),
                'created_at'      => $createdAt,
                'updated_at'      => $createdAt,
            ];
        }

        Click::insert($clicks);

        $this->info("Clics générés : {$clicksTarget}");

        return self::SUCCESS;
    }
}
