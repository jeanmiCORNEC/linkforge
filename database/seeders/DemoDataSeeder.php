<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Click;
use App\Models\Link;
use App\Models\Source;
use App\Models\TrackedLink;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    private User $demoUser;
    private array $campaigns = [];
    private array $sources = [];
    private array $links = [];
    private array $trackedLinks = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creating demo data for marketing screenshots...');

        $this->createDemoUser();
        $this->createCampaigns();
        $this->createSources();
        $this->createLinks();
        $this->createTrackedLinks();
        $this->createClicks();

        $this->command->info('✅ Demo data created successfully!');
        $this->command->info('📧 Email: demo@linkforge.fr');
        $this->command->info('🔑 Password: password');
    }

    private function createDemoUser(): void
    {
        $this->command->info('Creating demo user...');

        $this->demoUser = User::create([
            'name' => 'Compte Démo',
            'username' => 'demo',
            'email' => 'demo@linkforge.fr',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'plan' => 'pro', // Give demo user Pro plan for full access
        ]);
    }

    private function createCampaigns(): void
    {
        $this->command->info('Creating campaigns...');

        $campaignData = [
            [
                'name' => 'Black Friday 2024',
                'status' => 'active',
                'notes' => 'Campagne promotionnelle Black Friday avec codes promo',
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->addDays(5),
            ],
            [
                'name' => 'Lancement Ebook Productivité',
                'status' => 'active',
                'notes' => 'Lancement du nouvel ebook sur la productivité pour créateurs',
                'starts_at' => now()->subDays(15),
                'ends_at' => null,
            ],
            [
                'name' => 'Affiliation Amazon – Tech',
                'status' => 'active',
                'notes' => 'Produits tech et accessoires Amazon Associates',
                'starts_at' => now()->subDays(30),
                'ends_at' => null,
            ],
            [
                'name' => 'Partenariat Vinted',
                'status' => 'active',
                'notes' => 'Articles mode et vêtements sur Vinted',
                'starts_at' => now()->subDays(20),
                'ends_at' => null,
            ],
            [
                'name' => 'Tests TikTok Q4',
                'status' => 'active',
                'notes' => 'Tests de contenu TikTok Q4 2024',
                'starts_at' => now()->subDays(25),
                'ends_at' => now()->addDays(30),
            ],
            [
                'name' => 'Campagne YouTube Mensuelle',
                'status' => 'active',
                'notes' => 'Liens pour les vidéos YouTube du mois',
                'starts_at' => now()->subDays(20),
                'ends_at' => null,
            ],
        ];

        foreach ($campaignData as $data) {
            $this->campaigns[] = Campaign::create([
                'user_id' => $this->demoUser->id,
                ...$data,
            ]);
        }
    }

    private function createSources(): void
    {
        $this->command->info('Creating sources...');

        $sourcesData = [
            // Black Friday sources
            ['campaign_idx' => 0, 'name' => 'Bio TikTok – Setup 2025', 'platform' => 'tiktok'],
            ['campaign_idx' => 0, 'name' => 'Story Instagram – Code BF', 'platform' => 'instagram'],
            ['campaign_idx' => 0, 'name' => 'Post LinkedIn – Promo', 'platform' => 'linkedin'],
            ['campaign_idx' => 0, 'name' => 'Newsletter Email', 'platform' => null],

            // Ebook sources
            ['campaign_idx' => 1, 'name' => 'Vidéo YouTube – Unboxing', 'platform' => 'youtube'],
            ['campaign_idx' => 1, 'name' => 'Bio Instagram', 'platform' => 'instagram'],
            ['campaign_idx' => 1, 'name' => 'Thread Twitter/X', 'platform' => 'twitter'],

            // Amazon Tech sources
            ['campaign_idx' => 2, 'name' => 'Review YouTube – Gaming Setup', 'platform' => 'youtube'],
            ['campaign_idx' => 2, 'name' => 'TikTok – Top 5 Accessoires', 'platform' => 'tiktok'],
            ['campaign_idx' => 2, 'name' => 'Story Instagram – Test Micro', 'platform' => 'instagram'],
            ['campaign_idx' => 2, 'name' => 'Post Facebook – Recommandations', 'platform' => 'facebook'],

            // Vinted sources
            ['campaign_idx' => 3, 'name' => 'Story Instagram – Vinted Haul', 'platform' => 'instagram'],
            ['campaign_idx' => 3, 'name' => 'TikTok – Mode Seconde Main', 'platform' => 'tiktok'],
            ['campaign_idx' => 3, 'name' => 'Bio Vinted', 'platform' => 'vinted'],

            // TikTok Tests
            ['campaign_idx' => 4, 'name' => 'TikTok – Format Court #1', 'platform' => 'tiktok'],
            ['campaign_idx' => 4, 'name' => 'TikTok – Format Long #2', 'platform' => 'tiktok'],
            ['campaign_idx' => 4, 'name' => 'Bio TikTok Principal', 'platform' => 'tiktok'],

            // YouTube Monthly
            ['campaign_idx' => 5, 'name' => 'YouTube – Tuto Setup', 'platform' => 'youtube'],
            ['campaign_idx' => 5, 'name' => 'YouTube – Review Matériel', 'platform' => 'youtube'],
            ['campaign_idx' => 5, 'name' => 'Descriptif YouTube Commun', 'platform' => 'youtube'],
        ];

        foreach ($sourcesData as $data) {
            $this->sources[] = Source::create([
                'user_id' => $this->demoUser->id,
                'campaign_id' => $this->campaigns[$data['campaign_idx']]->id,
                'name' => $data['name'],
                'platform' => $data['platform'],
            ]);
        }
    }

    private function createLinks(): void
    {
        $this->command->info('Creating links...');

        $linksData = [
            ['title' => 'Ebook Productivité 2025', 'url' => 'https://example.com/ebook-productivite', 'slug' => 'ebook-prod'],
            ['title' => 'Amazon – Micro USB Fifine', 'url' => 'https://www.amazon.fr/dp/B07Y1C6GDS?tag=demo-21', 'slug' => 'micro-fifine'],
            ['title' => 'Amazon – Webcam Logitech C920', 'url' => 'https://www.amazon.fr/dp/B006A2Q81M?tag=demo-21', 'slug' => 'webcam-c920'],
            ['title' => 'Amazon – Clavier Gaming RGB', 'url' => 'https://www.amazon.fr/dp/B07ZGDPT4M?tag=demo-21', 'slug' => 'clavier-rgb'],
            ['title' => 'Vinted – Veste Vintage Nike', 'url' => 'https://www.vinted.fr/items/2863094562', 'slug' => 'veste-nike'],
            ['title' => 'Vinted – Sneakers Adidas', 'url' => 'https://www.vinted.fr/items/2987653421', 'slug' => 'sneakers-adidas'],
            ['title' => 'Formation Complète TikTok', 'url' => 'https://example.com/formation-tiktok', 'slug' => 'formation-tt'],
            ['title' => 'Landing Page Black Friday', 'url' => 'https://example.com/black-friday-2024', 'slug' => 'bf-2024'],
            ['title' => 'Amazon – Kit Éclairage LED', 'url' => 'https://www.amazon.fr/dp/B08C4YL6Z8?tag=demo-21', 'slug' => 'led-kit'],
            ['title' => 'Page Partenaire Vinted', 'url' => 'https://example.com/partenaire-vinted', 'slug' => 'partner-vinted'],
            ['title' => 'YouTube – Playlist Setup', 'url' => 'https://youtube.com/playlist?list=PLdemo123', 'slug' => 'yt-setup'],
            ['title' => 'Amazon – Support Smartphone', 'url' => 'https://www.amazon.fr/dp/B07D8K9ZKS?tag=demo-21', 'slug' => 'phone-holder'],
        ];

        foreach ($linksData as $data) {
            $this->links[] = Link::create([
                'user_id' => $this->demoUser->id,
                'title' => $data['title'],
                'destination_url' => $data['url'],
                'slug' => $data['slug'],
                'is_active' => true,
            ]);
        }
    }

    private function createTrackedLinks(): void
    {
        $this->command->info('Creating tracked links...');

        // Create combinations of links and sources
        $combinations = [
            // Black Friday campaign
            ['link_idx' => 7, 'source_idx' => 0], // Landing BF → TikTok
            ['link_idx' => 7, 'source_idx' => 1], // Landing BF → Instagram
            ['link_idx' => 7, 'source_idx' => 2], // Landing BF → LinkedIn
            ['link_idx' => 1, 'source_idx' => 3], // Micro → Newsletter

            // Ebook campaign
            ['link_idx' => 0, 'source_idx' => 4], // Ebook → YouTube
            ['link_idx' => 0, 'source_idx' => 5], // Ebook → Instagram
            ['link_idx' => 0, 'source_idx' => 6], // Ebook → Twitter

            // Amazon Tech
            ['link_idx' => 1, 'source_idx' => 7], // Micro → YouTube
            ['link_idx' => 2, 'source_idx' => 7], // Webcam → YouTube
            ['link_idx' => 3, 'source_idx' => 8], // Clavier → TikTok
            ['link_idx' => 8, 'source_idx' => 9], // LED Kit → Instagram
            ['link_idx' => 11, 'source_idx' => 10], // Phone holder → Facebook

            // Vinted
            ['link_idx' => 4, 'source_idx' => 11], // Veste → Instagram
            ['link_idx' => 5, 'source_idx' => 12], // Sneakers → TikTok
            ['link_idx' => 9, 'source_idx' => 13], // Partner page → Vinted bio

            // TikTok Tests
            ['link_idx' => 6, 'source_idx' => 14], // Formation → TikTok #1
            ['link_idx' => 6, 'source_idx' => 15], // Formation → TikTok #2
            ['link_idx' => 6, 'source_idx' => 16], // Formation → Bio TikTok

            // YouTube
            ['link_idx' => 10, 'source_idx' => 17], // Playlist → Tuto
            ['link_idx' => 2, 'source_idx' => 18], // Webcam → Review
            ['link_idx' => 1, 'source_idx' => 19], // Micro → Description
        ];

        foreach ($combinations as $combo) {
            $link = $this->links[$combo['link_idx']];
            $source = $this->sources[$combo['source_idx']];

            $this->trackedLinks[] = TrackedLink::create([
                'user_id' => $this->demoUser->id,
                'link_id' => $link->id,
                'source_id' => $source->id,
                'tracking_key' => 'lf_demo_' . strtolower(bin2hex(random_bytes(4))),
            ]);
        }
    }

    private function createClicks(): void
    {
        $this->command->info('Creating clicks...');

        $totalClicks = rand(2000, 3000);
        $this->command->info("Generating {$totalClicks} clicks...");

        $progressBar = $this->command->getOutput()->createProgressBar($totalClicks);
        $progressBar->start();

        for ($i = 0; $i < $totalClicks; $i++) {
            $trackedLink = $this->trackedLinks[array_rand($this->trackedLinks)];
            $timestamp = $this->generateRealisticTimestamp();

            Click::create([
                'tracked_link_id' => $trackedLink->id,
                'ip_address' => $this->generateRandomIp(),
                'user_agent' => $this->generateRandomUserAgent(),
                'referrer' => $this->generateRandomReferrer(),
                'country' => $country = $this->generateRandomCountry(),
                'city' => $this->generateRandomCity($country),
                'device' => $device = $this->generateRandomDevice(),
                'browser' => $this->generateRandomBrowser($device),
                'os' => $this->generateRandomOs($device),
                'visitor_hash' => md5(uniqid(rand(), true)),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
    }

    private function generateRealisticTimestamp(): \DateTime
    {
        $daysAgo = rand(0, 6); // Last 7 days
        
        // More recent days get more clicks
        $weights = [7, 6, 5, 4, 3, 2, 1]; // Today gets weight 7, 6 days ago gets weight 1
        $weightedDay = $this->weightedRandom($weights);
        
        $date = now()->subDays($weightedDay);
        
        // Peak hours: 10-14h and 19-22h
        $hour = rand(0, 100) < 60 
            ? rand(10, 22) // 60% during active hours
            : rand(0, 23);  // 40% random
        
        $minute = rand(0, 59);
        $second = rand(0, 59);
        
        return $date->setTime($hour, $minute, $second);
    }

    private function weightedRandom(array $weights): int
    {
        $total = array_sum($weights);
        $random = rand(1, $total);
        
        $sum = 0;
        foreach ($weights as $index => $weight) {
            $sum += $weight;
            if ($random <= $sum) {
                return $index;
            }
        }
        
        return 0;
    }

    private function generateRandomDevice(): string
    {
        $devices = ['mobile' => 70, 'desktop' => 25, 'tablet' => 5];
        return $this->weightedChoice($devices);
    }

    private function generateRandomCountry(): string
    {
        $countries = ['FR' => 60, 'CA' => 20, 'BE' => 10, 'CH' => 10];
        return $this->weightedChoice($countries);
    }

    private function generateRandomCity(string $country): string
    {
        $cities = [
            'FR' => ['Paris', 'Lyon', 'Marseille', 'Toulouse', 'Nice', 'Nantes', 'Bordeaux', 'Lille'],
            'CA' => ['Montréal', 'Québec', 'Toronto', 'Vancouver', 'Ottawa'],
            'BE' => ['Bruxelles', 'Anvers', 'Gand', 'Liège'],
            'CH' => ['Genève', 'Zurich', 'Lausanne', 'Berne'],
        ];

        $countryCities = $cities[$country] ?? ['Unknown'];
        return $countryCities[array_rand($countryCities)];
    }

    private function generateRandomBrowser(string $device): string
    {
        if ($device === 'mobile') {
            $browsers = ['Safari' => 40, 'Chrome' => 35, 'Samsung Internet' => 15, 'Firefox' => 10];
        } else {
            $browsers = ['Chrome' => 50, 'Safari' => 20, 'Firefox' => 15, 'Edge' => 15];
        }
        
        return $this->weightedChoice($browsers);
    }

    private function generateRandomOs(string $device): string
    {
        if ($device === 'mobile') {
            $os = ['iOS' => 55, 'Android' => 45];
        } elseif ($device === 'tablet') {
            $os = ['iOS' => 70, 'Android' => 30];
        } else {
            $os = ['Windows' => 60, 'macOS' => 30, 'Linux' => 10];
        }
        
        return $this->weightedChoice($os);
    }

    private function generateRandomUserAgent(): string
    {
        $userAgents = [
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Linux; Android 13) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.6045.66 Mobile Safari/537.36',
            'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/119.0',
        ];

        return $userAgents[array_rand($userAgents)];
    }

    private function generateRandomReferrer(): ?string
    {
        $referrers = [
            'https://www.tiktok.com',
            'https://www.instagram.com',
            'https://www.youtube.com',
            'https://www.facebook.com',
            'https://www.linkedin.com',
            'https://twitter.com',
            null, // Direct traffic
            null,
        ];

        return $referrers[array_rand($referrers)];
    }

    private function generateRandomIp(): string
    {
        return sprintf('%d.%d.%d.%d', rand(1, 255), rand(0, 255), rand(0, 255), rand(1, 255));
    }

    private function weightedChoice(array $choices): string
    {
        $total = array_sum($choices);
        $random = rand(1, $total);
        
        $sum = 0;
        foreach ($choices as $choice => $weight) {
            $sum += $weight;
            if ($random <= $sum) {
                return $choice;
            }
        }
        
        return array_key_first($choices);
    }
}
