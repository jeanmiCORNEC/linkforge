<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendWeeklyDigests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:weekly-digest';
    protected $description = 'Send weekly digest emails to users';

    public function handle(): int
    {
        $users = \App\Models\User::whereHas('links')->get();
        $start = \Illuminate\Support\Carbon::now()->subWeek()->startOfWeek();
        $end = \Illuminate\Support\Carbon::now()->subWeek()->endOfWeek();

        $this->info("Sending weekly digests for week {$start->format('Y-m-d')} to {$end->format('Y-m-d')}...");

        foreach ($users as $user) {
            // 1. Total clicks last week
            $totalClicks = \App\Models\Click::whereIn(
                'tracked_link_id',
                $user->trackedLinks()->select('id')
            )
            ->whereBetween('created_at', [$start, $end])
            ->count();

            // 2. Active links count
            $activeLinks = $user->links()->where('is_active', true)->count();

            // 3. Top 3 links
            $topLinks = $user->links()
                ->withCount(['clicks' => function ($query) use ($start, $end) {
                    $query->whereBetween('clicks.created_at', [$start, $end]);
                }])
                ->orderByDesc('clicks_count')
                ->take(3)
                ->get()
                ->map(function ($link) {
                    return [
                        'title' => $link->title,
                        'clicks' => $link->clicks_count,
                    ];
                });

            // Only send if there is activity or active links
            if ($activeLinks > 0 || $totalClicks > 0) {
                $stats = [
                    'total_clicks' => $totalClicks,
                    'active_links' => $activeLinks,
                    'top_links' => $topLinks,
                    'start_date' => $start->format('d/m/Y'),
                    'end_date' => $end->format('d/m/Y'),
                ];

                \Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\WeeklyDigestMail($stats));
                $this->info("Sent digest to {$user->email}");
            }
        }

        return self::SUCCESS;
    }
}
