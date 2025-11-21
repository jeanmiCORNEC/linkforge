<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendZeroClickAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:zero-click-alert {--hours=24 : Hours to check for zero clicks}';
    protected $description = 'Alert users if they have no clicks in the last X hours';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $threshold = \Illuminate\Support\Carbon::now()->subHours($hours);
        $cooldown = \Illuminate\Support\Carbon::now()->subDays(7);

        $users = \App\Models\User::whereHas('links')->get();

        $this->info("Checking for users with 0 clicks in the last {$hours} hours...");

        foreach ($users as $user) {
            // 1. Check rate limit (max 1 alert per week)
            if ($user->last_zero_click_alert_at && $user->last_zero_click_alert_at > $cooldown) {
                continue;
            }

            // 2. Check if user has EVER had clicks (don't alert new users with no traffic yet)
            $hasHistory = \App\Models\Click::whereIn(
                'tracked_link_id',
                $user->trackedLinks()->select('id')
            )->exists();

            if (!$hasHistory) {
                continue;
            }

            // 3. Check for recent clicks
            $hasRecentClicks = \App\Models\Click::whereIn(
                'tracked_link_id',
                $user->trackedLinks()->select('id')
            )
            ->where('created_at', '>=', $threshold)
            ->exists();

            if (!$hasRecentClicks) {
                // ALERT!
                $daysSinceLastClick = 0;
                $lastClick = \App\Models\Click::whereIn(
                    'tracked_link_id',
                    $user->trackedLinks()->select('id')
                )->latest('created_at')->first();

                if ($lastClick) {
                    $daysSinceLastClick = $lastClick->created_at->diffInDays();
                }

                \Illuminate\Support\Facades\Mail::to($user)->send(new \App\Mail\ZeroClickAlertMail($user, $daysSinceLastClick));
                
                $user->last_zero_click_alert_at = \Illuminate\Support\Carbon::now();
                $user->save();

                $this->warn("Sent zero-click alert to {$user->email}");
            }
        }

        return self::SUCCESS;
    }
}
